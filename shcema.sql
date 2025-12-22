CREATE DATABASE dental_appointment_system;

USE dental_appointment_system;

CREATE TABLE Patients (
    PatientID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100),
    IC VARCHAR(20),
    Sex CHAR(1),
    Address VARCHAR(255),
    Age INT,
    MobileNo VARCHAR(20),
    Email VARCHAR(255),
    Username VARCHAR(50) UNIQUE,
    Password VARCHAR(255)
);

CREATE TABLE Slots (
    SlotID INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE,
    Time TIME,
    Status ENUM('available', 'unavailable')
);

CREATE TABLE Appointments (
    AppointmentID INT AUTO_INCREMENT PRIMARY KEY,
    PatientID INT,
    Date DATE,
    Time TIME,
    Service VARCHAR(100),
    Status ENUM('Pending', 'Approved', 'Rejected'),
    SlotID INT,
    FOREIGN KEY (PatientID) REFERENCES Patients(PatientID) ON DELETE CASCADE,
    FOREIGN KEY (SlotID) REFERENCES Slots(SlotID) ON DELETE CASCADE
);

CREATE TABLE Admin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) UNIQUE,
    Password VARCHAR(255)
);

INSERT INTO Admin VALUES 
("", "admin", "admin");
