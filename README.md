# 🏥 Hospital Management System (HMS)

The **Hospital Management System** is a full-featured web application designed to streamline hospital operations by integrating patient registration, triage, doctor consultation, laboratory processing, and administrative management into one cohesive system. Built with **Laravel** and **Tailwind CSS**, the system ensures secure access control for different user roles such as doctors, nurses, lab technicians, and administrators.

---

## 🚀 Features

### 👨‍⚕️ For Doctors
- View consultation queue of assigned patients.
- Record and update diagnoses and prescriptions.
- Refer patients to laboratory or radiology departments.
- Access patient history and test results.

### 🧑‍⚕️ For Nurses (Triage)
- Record patient vital signs and preliminary assessments.
- Assign patients to doctors or departments.
- Track patient flow from triage to consultation.

### 🔬 For Laboratory / Radiology
- View all patients referred by doctors for lab or radiology tests.
- Process and input lab results.
- Update test status (Pending → In Progress → Completed).

### For Pharmacists
-View all prescriptions
-Bill 

### 🧑‍💼 For Administrators
- Manage users and roles (Doctor, Nurse, Lab Tech, Admin).
- Monitor hospital activities and view reports.
- Maintain scalability and data integrity.

### 🧾 For Patients (optional module)
- Online appointment booking.
- Access to visit and test history.
- Receive digital test results.

---

## 🏗️ System Architecture

**Tech Stack:**
- **Backend:** Laravel 10+
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Database:** MySQL / MariaDB
- **Server:** XAMPP / Apache
- **Version Control:** Git & GitHub

**Core Modules:**
1. Authentication & Role-Based Access Control (RBAC)
2. Patient Registration & Queue Management
3. Triage and Vital Recording
4. Doctor Consultation and Lab Referral
5. Laboratory / Radiology Workflow
6. Inpatient
7. Billing
8. Results and Reporting

---

## ⚙️ Installation Guide

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/markgee-ui/hms.git
cd hms
