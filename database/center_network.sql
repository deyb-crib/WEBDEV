USE katoc;

INSERT IGNORE INTO dialysis_centers
    (name, address, contact_number, email, operating_hours, dialysis_types, machine_count, available_slots, status)
VALUES
    ('Nephrology Center of Dumaguete City Dialysis, Inc.', 'Dumaguete City, Negros Oriental', '+63 917 123 4504', 'nephrology@katoc.local', '8:00 am - 5:00 pm', 'Hemodialysis, Peritoneal Dialysis', 18, 9, 'Active'),
    ('Bais Community Hospital', 'Bais City, Negros Oriental', '+63 917 123 4505', 'bais@katoc.local', '8:00 am - 5:00 pm', 'Hemodialysis', 12, 5, 'Active'),
    ('Tanjay Renal Care Center', 'Tanjay City, Negros Oriental', '+63 917 123 4506', 'tanjay@katoc.local', '8:00 am - 5:00 pm', 'Hemodialysis', 14, 7, 'Active'),
    ('Bayawan Medical Center', 'Bayawan City, Negros Oriental', '+63 917 123 4507', 'bayawan@katoc.local', '8:00 am - 5:00 pm', 'Hemodialysis, Peritoneal Dialysis', 10, 4, 'Active'),
    ('Dumaguete Kidney Institute', 'Dumaguete City, Negros Oriental', '+63 917 123 4508', 'kidney.institute@katoc.local', '8:00 am - 5:00 pm', 'Hemodialysis, Peritoneal Dialysis', 22, 11, 'Active');
