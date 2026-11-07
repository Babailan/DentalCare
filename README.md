# Dental Appointment Booking System 🦷📅

This repository contains a **Dental Appointment Booking System** developed for academic purpose. The system aims to improve appointment scheduling efficiency for dental clinics by replacing manual booking processes with an online system.

---

## Features

### User (Patient)
- Register and login
- View available appointment slots
- Book and cancel appointments
- View appointment history
- Chat with the clinic administrator
- Update personal profile

### Admin
- Login authentication
- Manage patient records (view, edit, delete)
- Approve or reject appointments
- Manage appointment slots
- View daily appointments
- Chat with patients

---

## System Objectives

- Reduce manual workload for dental clinic staff
- Allow patients to book appointments online at any time
- Prevent scheduling conflicts and booking errors
- Improve overall clinic efficiency and patient experience

---

## How to run this system locally 🖥️

1. Install and open XAMPP.

2. Start the **Apache** service. MySQL/phpMyAdmin is no longer required.

3. Copy the project folder into:

```
C:\xampp\htdocs\{project_folder}
```

4. Open the system in your browser:
```
http://localhost/{project_folder}/public/index.php
```

5. The system is now ready for testing.

### Run on port 4000

Double-click `run.bat` (or run it from a terminal). It starts the PHP built-in server at:

```
http://localhost:4000/public/index.php
```

Press `Ctrl+C` in the terminal to stop the server.

### Database

The system uses a SQLite database stored at:

```
database/dental_appointment_system.db
```

It is created automatically from `database/schema.sql` the first time the application loads. To reset all data, delete `database/dental_appointment_system.db` and reload the page.

This requires PHP's `pdo_sqlite` extension, which is enabled by default in XAMPP.

Default administrator account:

- Username: `admin`
- Password: `admin`

---

## Project Scope
- Use Case Diagram

<img width="639" height="373" alt="Use Case Diagram" src="https://github.com/user-attachments/assets/2ac99c3e-f74a-456a-a2c5-354505c85864" />


- Context Diagram

<img width="678" height="287" alt="Context Diagram" src="https://github.com/user-attachments/assets/99a8464b-ff48-4558-a2c1-9b3981e2c50d" />


- Class Diagram

<img width="619" height="641" alt="Class Diagram" src="https://github.com/user-attachments/assets/b5322b3d-f7e4-4415-9f61-3536858cbd59" />


---

## Some examples of the system UI
- Login Page

<img width="579" height="274" alt="Login UI" src="https://github.com/user-attachments/assets/33120362-aaec-466f-825c-690bc7920810" />


- User Home Page

<img width="563" height="224" alt="home1" src="https://github.com/user-attachments/assets/cb05bebf-2946-4391-bbff-ce176b3fd24b" />
<img width="564" height="120" alt="home2" src="https://github.com/user-attachments/assets/069ea0a6-34a4-479c-b015-4f64d94474de" />
<img width="562" height="216" alt="home3" src="https://github.com/user-attachments/assets/7b86de47-f43b-445e-b3ed-3042da16864b" />
<img width="562" height="207" alt="home4" src="https://github.com/user-attachments/assets/3e29939c-360a-48c0-bcdb-fd019796ab60" />
<img width="563" height="59" alt="home5" src="https://github.com/user-attachments/assets/7fbc6b99-5697-499a-af2c-c8270550473b" />


- User Appointment page

<img width="542" height="256" alt="user-appointment" src="https://github.com/user-attachments/assets/a3073830-9f3c-40a6-bacb-a5d83c8a8a8c" />


- Admin Dashboard

<img width="575" height="266" alt="admin-dashboard" src="https://github.com/user-attachments/assets/227e35c2-a59e-4d75-bcd4-40975f35021f" />


- Admin Appointment page

<img width="543" height="256" alt="admin-appointment" src="https://github.com/user-attachments/assets/7be93ddd-e820-49c0-8d27-9a6d2656fe63" />


---

## Notes

This project is developed for **academic and learning purposes**. It is not intended for direct production use without further security, performance, and compliance enhancements.
