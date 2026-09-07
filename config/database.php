<?php
declare(strict_types=1);

final class JsonDatabaseStatement
{
    private JsonDatabase $database;
    private string $sql;
    private ?array $result = null;

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

            return true;
        }

        throw new PDOException('Unsupported database query: ' . $normalizedSql);
    }

    public function fetch(): ?array
    {
        return $this->result;
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

    public function getAppointments(): array
    {
        $contents = file_get_contents($this->appointmentsFilePath);
        $appointments = $contents ? json_decode($contents, true) : [];

        return is_array($appointments) ? $appointments : [];
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
    $password = getenv('KATOC_DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
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
                (appointment_reference, user_id, center_name, center_location, dialysis_type,
                 appointment_date, session, appointment_time, patient_name, contact_number,
                 email, emergency_contact_name, emergency_contact_number, notes, status)
             VALUES
                (:appointment_reference, :user_id, :center_name, :center_location, :dialysis_type,
                 :appointment_date, :session, :appointment_time, :patient_name, :contact_number,
                 :email, :emergency_contact_name, :emergency_contact_number, :notes, :status)'
        );
        $insert->execute([
            'appointment_reference' => 'TEMP-' . bin2hex(random_bytes(8)),
            'user_id' => $appointment['user_id'],
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
