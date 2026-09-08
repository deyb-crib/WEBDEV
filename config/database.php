<?php
declare(strict_types=1);

final class JsonDatabaseStatement
{
    private JsonDatabase $database;
    private string $sql;
    private ?array $result = null;
    private int $rowCount = 0;

    public function __construct(JsonDatabase $database, string $sql)
    {
        $this->database = $database;
        $this->sql = $sql;
    }

    public function execute(array $params = []): bool
    {
        $normalizedSql = trim($this->sql);
        $upperSql = strtoupper($normalizedSql);

        if (str_contains($upperSql, 'SELECT') && str_contains($upperSql, 'FROM USERS') && str_contains($upperSql, 'WHERE EMAIL')) {
            $email = trim((string) ($params['email'] ?? ''));
            $user = $this->database->findUserByEmail($email);
            $this->result = $user ? [
                'id' => (string) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'password_hash' => $user['password_hash'],
            ] : null;
            $this->rowCount = $this->result ? 1 : 0;

            return true;
        }

        if (str_contains($upperSql, 'INSERT INTO USERS')) {
            $name = trim((string) ($params['name'] ?? ''));
            $email = strtolower(trim((string) ($params['email'] ?? '')));
            $passwordHash = (string) ($params['password_hash'] ?? '');

            if ($name === '' || $email === '' || $passwordHash === '') {
                throw new PDOException('Missing required user data.');
            }

            $this->database->createUser($name, $email, $passwordHash);
            $this->result = null;
            $this->rowCount = 1;

            return true;
        }

        if (str_contains($upperSql, 'INSERT INTO DIALYSIS_CENTERS')) {
            $centerId = $this->database->createFallbackCenterFromInput($params);
            $this->result = $centerId > 0 ? ['id' => $centerId] : null;
            $this->rowCount = $centerId > 0 ? 1 : 0;

            return true;
        }

        if (str_contains($upperSql, 'UPDATE DIALYSIS_CENTERS')) {
            $this->rowCount = $this->database->updateFallbackCenterFromQuery($normalizedSql, $params);
            $this->result = null;

            return true;
        }

        if (str_contains($upperSql, 'SELECT') && str_contains($upperSql, 'FROM DIALYSIS_CENTERS')) {
            $this->result = $this->database->selectCentersFromQuery($normalizedSql, $params);
            $this->rowCount = is_array($this->result) ? count($this->result) : ($this->result ?? 0);

            return true;
        }

        if (str_contains($upperSql, 'SELECT') && str_contains($upperSql, 'FROM APPOINTMENTS')) {
            $this->result = $this->database->selectAppointmentsFromQuery($normalizedSql, $params);
            $this->rowCount = is_array($this->result) ? count($this->result) : ($this->result ?? 0);

            return true;
        }

        throw new PDOException('Unsupported database query: ' . $normalizedSql);
    }

    public function fetch(): ?array
    {
        if ($this->result === null) {
            $this->execute();
        }

        if (is_array($this->result) && array_is_list($this->result) && count($this->result) > 0 && is_array($this->result[0])) {
            return $this->result[0];
        }

        return is_array($this->result) ? $this->result : null;
    }

    public function fetchAll(): array
    {
        if ($this->result === null) {
            $this->execute();
        }

        return is_array($this->result) ? $this->result : [];
    }

    public function fetchColumn()
    {
        if ($this->result === null) {
            $this->execute();
        }

        if (is_array($this->result) && array_is_list($this->result) && count($this->result) > 0 && is_array($this->result[0])) {
            $firstRow = $this->result[0];
            $firstValue = reset($firstRow);
            return $firstValue === false ? null : $firstValue;
        }

        return $this->result;
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }
}

final class JsonDatabase
{
    private string $filePath;
    private string $appointmentsFilePath;
    private int $lastInsertId = 0;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../data/users.json';
        $this->appointmentsFilePath = __DIR__ . '/../data/appointments.json';

        if (!is_dir(__DIR__ . '/../data')) {
            mkdir(__DIR__ . '/../data', 0777, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, "[]");
        }

        if (!file_exists($this->appointmentsFilePath)) {
            file_put_contents($this->appointmentsFilePath, "[]");
        }
    }

    public function prepare(string $sql): JsonDatabaseStatement
    {
        return new JsonDatabaseStatement($this, $sql);
    }

    public function query(string $sql): JsonDatabaseStatement
    {
        return new JsonDatabaseStatement($this, $sql);
    }

    public function lastInsertId(): int
    {
        return $this->lastInsertId;
    }

    public function findUserByEmail(string $email): ?array
    {
        $users = $this->readUsers();
        $normalizedEmail = strtolower(trim($email));

        foreach ($users as $user) {
            if (isset($user['email']) && strtolower((string) $user['email']) === $normalizedEmail) {
                return $user;
            }
        }

        return null;
    }

    public function createUser(string $name, string $email, string $passwordHash): void
    {
        $users = $this->readUsers();
        $normalizedEmail = strtolower(trim($email));

        foreach ($users as $user) {
            if (isset($user['email']) && strtolower((string) $user['email']) === $normalizedEmail) {
                throw new PDOException('User already exists.');
            }
        }

        $nextId = 1;
        foreach ($users as $user) {
            $nextId = max($nextId, (int) ($user['id'] ?? 0) + 1);
        }

        $users[] = [
            'id' => $nextId,
            'name' => $name,
            'email' => $normalizedEmail,
            'password_hash' => $passwordHash,
            'created_at' => date('c'),
        ];

        $this->writeUsers($users);
        $this->lastInsertId = $nextId;
    }

    public function createAppointment(array $appointment): int
    {
        $contents = file_get_contents($this->appointmentsFilePath);
        $appointments = $contents ? json_decode($contents, true) : [];
        $appointments = is_array($appointments) ? $appointments : [];

        $nextId = 1;
        foreach ($appointments as $existingAppointment) {
            $nextId = max($nextId, (int) ($existingAppointment['id'] ?? 0) + 1);
        }

        $appointment['id'] = $nextId;
        $appointment['reference'] = $appointment['reference'] ?? sprintf('KATOC-%s-%04d', date('Y'), $nextId);
        $appointment['created_at'] = date('c');
        $appointments[] = $appointment;

        $encoded = json_encode($appointments, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || file_put_contents($this->appointmentsFilePath, $encoded, LOCK_EX) === false) {
            throw new PDOException('Unable to save appointment.');
        }

        return $nextId;
    }

    public function createFallbackCenterFromInput(array $params): int
    {
        $fields = [
            'facility_type' => 'facility_type',
            'name' => 'name',
            'address' => 'address',
            'contact' => 'contact_number',
            'contact_number' => 'contact_number',
            'email' => 'email',
            'hours' => 'operating_hours',
            'operating_hours' => 'operating_hours',
            'types' => 'dialysis_types',
            'dialysis_types' => 'dialysis_types',
            'image' => 'image_path',
            'image_path' => 'image_path',
            'machines' => 'machine_count',
            'machine_count' => 'machine_count',
            'slots' => 'available_slots',
            'available_slots' => 'available_slots',
            'status' => 'status',
        ];

        $center = [
            'facility_type' => 'Dialysis Center',
            'name' => '',
            'address' => '',
            'contact_number' => '',
            'email' => '',
            'operating_hours' => '',
            'dialysis_types' => '',
            'image_path' => null,
            'machine_count' => 0,
            'available_slots' => 0,
            'status' => 'Active',
        ];

        foreach ($params as $key => $value) {
            if (isset($fields[$key])) {
                $center[$fields[$key]] = $value;
            }
        }

        $center['name'] = trim((string) ($center['name'] ?? ''));
        if ($center['name'] === '') {
            throw new PDOException('Missing center name.');
        }

        $existingCenters = getFallbackCenters();
        foreach ($existingCenters as $existingCenter) {
            if (strtolower((string) ($existingCenter['name'] ?? '')) === strtolower($center['name'])) {
                $center['id'] = (int) ($existingCenter['id'] ?? 0);
                break;
            }
        }

        if (!isset($center['id'])) {
            $nextId = 1;
            foreach ($existingCenters as $existingCenter) {
                $nextId = max($nextId, (int) ($existingCenter['id'] ?? 0) + 1);
            }
            $center['id'] = $nextId;
        }

        $center['created_at'] = date('c');
        $updatedCenters = $existingCenters;
        $replaced = false;

        foreach ($updatedCenters as $index => $existingCenter) {
            if ((int) ($existingCenter['id'] ?? 0) === (int) $center['id']) {
                $updatedCenters[$index] = array_merge($existingCenter, $center);
                $replaced = true;
                break;
            }
        }

        if (!$replaced) {
            $updatedCenters[] = $center;
        }

        saveFallbackCenters($updatedCenters);

        return (int) $center['id'];
    }

    public function updateFallbackCenterFromQuery(string $sql, array $params): int
    {
        $id = filter_var($params['id'] ?? $params['center_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            return 0;
        }

        $centers = getFallbackCenters();
        $updated = 0;

        foreach ($centers as $index => $existingCenter) {
            if ((int) ($existingCenter['id'] ?? 0) !== $id) {
                continue;
            }

            $fieldMap = [
                'facility_type' => 'facility_type',
                'name' => 'name',
                'address' => 'address',
                'contact' => 'contact_number',
                'contact_number' => 'contact_number',
                'email' => 'email',
                'hours' => 'operating_hours',
                'operating_hours' => 'operating_hours',
                'types' => 'dialysis_types',
                'dialysis_types' => 'dialysis_types',
                'image' => 'image_path',
                'image_path' => 'image_path',
                'machines' => 'machine_count',
                'machine_count' => 'machine_count',
                'slots' => 'available_slots',
                'available_slots' => 'available_slots',
                'status' => 'status',
            ];

            foreach ($params as $key => $value) {
                if (isset($fieldMap[$key])) {
                    $centers[$index][$fieldMap[$key]] = $value;
                }
            }

            if (str_contains(strtoupper($sql), 'SET STATUS = \'INACTIVE\'')) {
                $centers[$index]['status'] = 'Inactive';
            }

            if (str_contains(strtoupper($sql), 'SET STATUS = \'ACTIVE\'')) {
                $centers[$index]['status'] = 'Active';
            }

            $updated = 1;
            break;
        }

        if ($updated === 1) {
            saveFallbackCenters($centers);
        }

        return $updated;
    }

    public function selectCentersFromQuery(string $sql, array $params = []): array|int
    {
        $upperSql = strtoupper($sql);
        $centers = getFallbackCenters();

        if (str_contains($upperSql, 'COUNT(*)')) {
            $filtered = $this->filterFallbackCenters($centers, $sql, $params);
            return count($filtered);
        }

        $filtered = $this->filterFallbackCenters($centers, $sql, $params);

        if (str_contains($upperSql, 'ORDER BY NAME DESC')) {
            usort($filtered, static fn (array $a, array $b): int => strcmp((string) ($b['name'] ?? ''), (string) ($a['name'] ?? '')));
        } elseif (str_contains($upperSql, 'ORDER BY NAME')) {
            usort($filtered, static fn (array $a, array $b): int => strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));
        }

        return array_values($filtered);
    }

    public function selectAppointmentsFromQuery(string $sql, array $params = []): array|int
    {
        $upperSql = strtoupper($sql);
        $appointments = $this->getAppointments();

        if (str_contains($upperSql, 'COUNT(*)')) {
            $filtered = $this->filterAppointments($appointments, $sql, $params);
            return count($filtered);
        }

        $filtered = $this->filterAppointments($appointments, $sql, $params);
        if (str_contains($upperSql, 'ORDER BY A.CREATED_AT DESC')) {
            usort($filtered, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
        }

        return array_values($filtered);
    }

    public function getAppointments(): array
    {
        $contents = file_get_contents($this->appointmentsFilePath);
        $appointments = $contents ? json_decode($contents, true) : [];

        return is_array($appointments) ? $appointments : [];
    }

    private function filterFallbackCenters(array $centers, string $sql, array $params): array
    {
        $upperSql = strtoupper($sql);
        $filtered = $centers;

        if (str_contains($upperSql, 'WHERE STATUS = \'ACTIVE\'')) {
            $filtered = array_values(array_filter(
                $filtered,
                static fn (array $center): bool => strtolower((string) ($center['status'] ?? 'Active')) === 'active'
            ));
        }

        if (isset($params['id']) || isset($params['center_id'])) {
            $id = filter_var($params['id'] ?? $params['center_id'] ?? 0, FILTER_VALIDATE_INT);
            if ($id !== false) {
                $filtered = array_values(array_filter(
                    $filtered,
                    static fn (array $center): bool => (int) ($center['id'] ?? 0) === $id
                ));
            }
        }

        if (isset($params['name'])) {
            $name = trim((string) $params['name']);
            if ($name !== '') {
                $filtered = array_values(array_filter(
                    $filtered,
                    static fn (array $center): bool => strtolower((string) ($center['name'] ?? '')) === strtolower($name)
                ));
            }
        }

        if (isset($params['name_search']) || isset($params['address_search'])) {
            $searchName = trim((string) ($params['name_search'] ?? ''));
            $searchAddress = trim((string) ($params['address_search'] ?? ''));
            $filtered = array_values(array_filter(
                $filtered,
                static function (array $center) use ($searchName, $searchAddress): bool {
                    $nameMatches = $searchName === '' || str_contains(strtolower((string) ($center['name'] ?? '')), strtolower($searchName));
                    $addressMatches = $searchAddress === '' || str_contains(strtolower((string) ($center['address'] ?? '')), strtolower($searchAddress));

                    return $nameMatches || $addressMatches;
                }
            ));
        }

        if (str_contains($upperSql, 'LIMIT 1')) {
            return array_slice($filtered, 0, 1);
        }

        return $filtered;
    }

    private function filterAppointments(array $appointments, string $sql, array $params): array
    {
        $upperSql = strtoupper($sql);
        $filtered = $appointments;

        if (isset($params['user_id'])) {
            $userId = filter_var($params['user_id'], FILTER_VALIDATE_INT);
            if ($userId !== false) {
                $filtered = array_values(array_filter(
                    $filtered,
                    static fn (array $appointment): bool => (int) ($appointment['user_id'] ?? 0) === $userId
                ));
            }
        }

        if (isset($params['appointment_id'])) {
            $appointmentId = filter_var($params['appointment_id'], FILTER_VALIDATE_INT);
            if ($appointmentId !== false) {
                $filtered = array_values(array_filter(
                    $filtered,
                    static fn (array $appointment): bool => (int) ($appointment['id'] ?? 0) === $appointmentId
                ));
            }
        }

        if (str_contains($upperSql, 'STATUS <> \'CANCELLED\'')) {
            $filtered = array_values(array_filter(
                $filtered,
                static fn (array $appointment): bool => strtolower((string) ($appointment['status'] ?? '')) !== 'cancelled'
            ));
        }

        return $filtered;
    }

    private function readUsers(): array
    {
        $contents = file_get_contents($this->filePath);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function writeUsers(array $users): void
    {
        $encoded = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new PDOException('Unable to save user data.');
        }

        if (file_put_contents($this->filePath, $encoded) === false) {
            throw new PDOException('Unable to write user data.');
        }
    }
}

function getDatabaseConnection()
{
    static $connection;

    if ($connection instanceof JsonDatabase) {
        return $connection;
    }

    $connection = new JsonDatabase();

    return $connection;
}

function getMySqlConnection(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('KATOC_DB_HOST') ?: '127.0.0.1';
    $port = getenv('KATOC_DB_PORT') ?: '3306';
    $database = getenv('KATOC_DB_NAME') ?: 'katoc';
    $username = getenv('KATOC_DB_USER') ?: 'root';
    $envPassword = getenv('KATOC_DB_PASSWORD');
    $passwordsToTry = $envPassword !== false ? [$envPassword] : ['102006', ''];

    $lastException = null;
    foreach ($passwordsToTry as $password) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $connection;
        } catch (PDOException $e) {
            $lastException = $e;
        }
    }

    throw $lastException ?? new PDOException('Could not connect to MySQL database.');
}

function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function isValidCsrfToken(string $token): bool
{
    return $token !== '' && hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}

function getConfiguredDialysisCenters(): array
{
    return [
        'Love Center' => ['location' => 'Dumaguete City, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'Nephrology Center of Dumaguete City Dialysis, Inc.' => ['location' => 'Dumaguete City, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'HemoCent' => ['location' => 'Valencia, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'Sibulan Dialysis Center' => ['location' => 'Sibulan, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'Bais Community Hospital' => ['location' => 'Bais City, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'Tanjay Renal Care Center' => ['location' => 'Tanjay City, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'Bayawan Medical Center' => ['location' => 'Bayawan City, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
        'Dumaguete Kidney Institute' => ['location' => 'Dumaguete City, Negros Oriental', 'hours' => '8:00 am - 5:00 pm'],
    ];
}

function getFallbackCentersStoragePath(): string
{
    return __DIR__ . '/../data/centers.json';
}

function saveFallbackCenters(array $centers): void
{
    $directory = dirname(getFallbackCentersStoragePath());
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $encoded = json_encode($centers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        throw new PDOException('Unable to encode fallback center data.');
    }

    if (file_put_contents(getFallbackCentersStoragePath(), $encoded) === false) {
        throw new PDOException('Unable to save fallback center data.');
    }
}

function getFallbackCenters(): array
{
    $path = getFallbackCentersStoragePath();
    $directory = dirname($path);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    if (!file_exists($path)) {
        $defaults = [];
        foreach (getConfiguredDialysisCenters() as $name => $details) {
            $defaults[] = [
                'id' => count($defaults) + 1,
                'facility_type' => 'Dialysis Center',
                'name' => $name,
                'address' => $details['location'],
                'contact_number' => '',
                'email' => '',
                'operating_hours' => $details['hours'],
                'dialysis_types' => 'Hemodialysis',
                'image_path' => null,
                'machine_count' => 0,
                'available_slots' => 0,
                'status' => 'Active',
                'created_at' => date('c'),
            ];
        }

        saveFallbackCenters($defaults);
    }

    $contents = file_get_contents($path);
    $decoded = $contents === false || trim($contents) === '' ? [] : json_decode($contents, true);

    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_filter($decoded, static fn (array $center): bool => !empty($center['name'])));
}

function getActiveFallbackCenters(): array
{
    return array_values(array_filter(
        getFallbackCenters(),
        static fn (array $center): bool => (($center['status'] ?? 'Active') === 'Active')
    ));
}

function upsertFallbackCenter(array $center): void
{
    $normalizedCenter = [
        'id' => (int) ($center['id'] ?? 0),
        'facility_type' => $center['facility_type'] ?? 'Dialysis Center',
        'name' => trim((string) ($center['name'] ?? '')),
        'address' => trim((string) ($center['address'] ?? '')),
        'contact_number' => trim((string) ($center['contact_number'] ?? '')),
        'email' => strtolower(trim((string) ($center['email'] ?? ''))),
        'operating_hours' => trim((string) ($center['operating_hours'] ?? '')),
        'dialysis_types' => trim((string) ($center['dialysis_types'] ?? '')),
        'image_path' => $center['image_path'] ?? null,
        'machine_count' => (int) ($center['machine_count'] ?? 0),
        'available_slots' => (int) ($center['available_slots'] ?? 0),
        'status' => $center['status'] ?? 'Active',
        'created_at' => $center['created_at'] ?? date('c'),
    ];

    $name = $normalizedCenter['name'];
    if ($name === '') {
        return;
    }

    $centers = getFallbackCenters();
    $updated = false;

    foreach ($centers as $index => $existingCenter) {
        if (strtolower((string) ($existingCenter['name'] ?? '')) === strtolower($name)) {
            $centers[$index] = array_merge($existingCenter, $normalizedCenter);
            $updated = true;
            break;
        }
    }

    if (!$updated) {
        $nextId = 1;
        foreach ($centers as $existingCenter) {
            $nextId = max($nextId, (int) ($existingCenter['id'] ?? 0) + 1);
        }

        $normalizedCenter['id'] = $nextId;
        $centers[] = $normalizedCenter;
    }

    saveFallbackCenters($centers);
}

function getFallbackCenterByName(string $name): ?array
{
    $normalizedName = trim($name);
    if ($normalizedName === '') {
        return null;
    }

    foreach (getActiveFallbackCenters() as $center) {
        if (strtolower((string) ($center['name'] ?? '')) === strtolower($normalizedName)) {
            return $center;
        }
    }

    return null;
}

function isConfiguredDialysisCenter(string $name, string $location, string $hours): bool
{
    $centers = getConfiguredDialysisCenters();

    return isset($centers[$name])
        && hash_equals($centers[$name]['location'], $location)
        && hash_equals($centers[$name]['hours'], $hours);
}

function createMySqlAppointment(array $appointment): array
{
    $connection = getMySqlConnection();
    $connection->beginTransaction();

    try {
        $slot = $connection->prepare(
            "SELECT id FROM appointments
             WHERE center_name = :center_name
               AND appointment_date = :appointment_date
               AND appointment_time = :appointment_time
               AND status <> 'Cancelled'
             FOR UPDATE"
        );
        $slot->execute([
            'center_name' => $appointment['center_name'],
            'appointment_date' => $appointment['appointment_date'],
            'appointment_time' => $appointment['appointment_time'],
        ]);

        if ($slot->fetch()) {
            throw new RuntimeException('slot_unavailable');
        }

        $insert = $connection->prepare(
            'INSERT INTO appointments
                (appointment_reference, user_id, hospital_id, center_name, center_location, dialysis_type,
                 appointment_date, session, appointment_time, patient_name, contact_number,
                 email, emergency_contact_name, emergency_contact_number, notes, status)
             VALUES
                (:appointment_reference, :user_id, :hospital_id, :center_name, :center_location, :dialysis_type,
                 :appointment_date, :session, :appointment_time, :patient_name, :contact_number,
                 :email, :emergency_contact_name, :emergency_contact_number, :notes, :status)'
        );
        $insert->execute([
            'appointment_reference' => 'TEMP-' . bin2hex(random_bytes(8)),
            'user_id' => $appointment['user_id'],
            'hospital_id' => $appointment['hospital_id'],
            'center_name' => $appointment['center_name'],
            'center_location' => $appointment['center_location'],
            'dialysis_type' => $appointment['dialysis_type'],
            'appointment_date' => $appointment['appointment_date'],
            'session' => $appointment['session'],
            'appointment_time' => $appointment['appointment_time'],
            'patient_name' => $appointment['patient_name'],
            'contact_number' => $appointment['contact_number'],
            'email' => $appointment['email'],
            'emergency_contact_name' => $appointment['emergency_contact_name'],
            'emergency_contact_number' => $appointment['emergency_contact_number'],
            'notes' => $appointment['notes'],
            'status' => 'Pending Confirmation',
        ]);

        $id = (int) $connection->lastInsertId();
        $reference = sprintf('KATOC-%s-%04d', date('Y'), $id);
        $update = $connection->prepare('UPDATE appointments SET appointment_reference = :reference WHERE id = :id');
        $update->execute(['reference' => $reference, 'id' => $id]);
        $connection->commit();

        return ['id' => $id, 'reference' => $reference];
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}

function getMySqlAppointmentsForUser(int $userId): array
{
    $statement = getMySqlConnection()->prepare(
        'SELECT * FROM appointments WHERE user_id = :user_id ORDER BY appointment_date DESC, appointment_time DESC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function cancelMySqlAppointment(int $appointmentId, int $userId): bool
{
    $statement = getMySqlConnection()->prepare(
        "UPDATE appointments SET status = 'Cancelled'
         WHERE id = :id AND user_id = :user_id AND status IN ('Pending Confirmation', 'Confirmed')"
    );
    $statement->execute(['id' => $appointmentId, 'user_id' => $userId]);

    return $statement->rowCount() === 1;
}

function updateMySqlAppointmentSchedule(int $appointmentId, int $userId, string $date, string $time): string
{
    $connection = getMySqlConnection();
    $connection->beginTransaction();

    try {
        $appointmentStatement = $connection->prepare(
            "SELECT center_name FROM appointments
             WHERE id = :id AND user_id = :user_id AND status IN ('Pending Confirmation', 'Confirmed')
             FOR UPDATE"
        );
        $appointmentStatement->execute(['id' => $appointmentId, 'user_id' => $userId]);
        $appointment = $appointmentStatement->fetch();

        if (!$appointment) {
            throw new RuntimeException('appointment_unavailable');
        }

        $slotStatement = $connection->prepare(
            "SELECT id FROM appointments
             WHERE center_name = :center_name AND appointment_date = :appointment_date
               AND appointment_time = :appointment_time AND status <> 'Cancelled' AND id <> :id
             FOR UPDATE"
        );
        $slotStatement->execute([
            'center_name' => $appointment['center_name'],
            'appointment_date' => $date,
            'appointment_time' => $time,
            'id' => $appointmentId,
        ]);

        if ($slotStatement->fetch()) {
            throw new RuntimeException('slot_unavailable');
        }

        $update = $connection->prepare(
            'UPDATE appointments SET appointment_date = :appointment_date, appointment_time = :appointment_time
             WHERE id = :id AND user_id = :user_id'
        );
        $update->execute([
            'appointment_date' => $date,
            'appointment_time' => $time,
            'id' => $appointmentId,
            'user_id' => $userId,
        ]);
        $connection->commit();

        return 'rescheduled';
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}
