-- SQLite schema for the Dental Appointment Booking System.
-- The application creates/updates this database automatically on first run.

CREATE TABLE IF NOT EXISTS Patients (
    PatientID INTEGER PRIMARY KEY AUTOINCREMENT,
    Name TEXT,
    IC TEXT,
    Sex TEXT CHECK (Sex IN ('Male', 'Female') OR Sex IS NULL),
    Address TEXT,
    Age INTEGER,
    MobileNo TEXT,
    Email TEXT,
    Username TEXT UNIQUE,
    Password TEXT
);

CREATE TABLE IF NOT EXISTS Slots (
    SlotID INTEGER PRIMARY KEY AUTOINCREMENT,
    Date TEXT,
    Time TEXT,
    Status TEXT CHECK (Status IN ('available', 'unavailable'))
);

CREATE TABLE IF NOT EXISTS Appointments (
    AppointmentID INTEGER PRIMARY KEY AUTOINCREMENT,
    PatientID INTEGER,
    Date TEXT,
    Time TEXT,
    Service TEXT,
    Status TEXT CHECK (Status IN ('Pending', 'Approved', 'Rejected')),
    SlotID INTEGER,
    FOREIGN KEY (PatientID) REFERENCES Patients(PatientID) ON DELETE CASCADE,
    FOREIGN KEY (SlotID) REFERENCES Slots(SlotID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Admin (
    AdminID INTEGER PRIMARY KEY AUTOINCREMENT,
    Username TEXT UNIQUE,
    Password TEXT
);

CREATE TABLE IF NOT EXISTS Messages (
    MessageID INTEGER PRIMARY KEY AUTOINCREMENT,
    PatientID INTEGER NOT NULL,
    Sender TEXT NOT NULL CHECK (Sender IN ('patient', 'admin')),
    Message TEXT NOT NULL,
    IsRead INTEGER NOT NULL DEFAULT 0,
    CreatedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PatientID) REFERENCES Patients(PatientID) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_messages_patient ON Messages (PatientID, MessageID);

-- Default administrator account (username: admin, password: admin)
INSERT OR IGNORE INTO Admin (AdminID, Username, Password) VALUES (1, 'admin', 'admin');
