-- Drop all tables
DROP TABLE IF EXISTS scholarship_applications;
DROP TABLE IF EXISTS students;

-- Create students table
CREATE TABLE students (
  id int(11) NOT NULL AUTO_INCREMENT,
  student_id varchar(50) NOT NULL UNIQUE,
  full_name varchar(100) NOT NULL,
  email varchar(100) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  course varchar(100) NOT NULL,
  year_level varchar(20) NOT NULL,
  contact_number varchar(20) DEFAULT NULL,
  address text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create scholarship_applications table
CREATE TABLE scholarship_applications (
  id int(11) NOT NULL AUTO_INCREMENT,
  full_name varchar(100) NOT NULL,
  student_id varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  course varchar(100) NOT NULL,
  year_level varchar(20) NOT NULL,
  gpa decimal(3,2) NOT NULL,
  scholarship_type varchar(100) NOT NULL,
  address text DEFAULT NULL,
  contact_number varchar(20) DEFAULT NULL,
  status varchar(20) DEFAULT 'Pending',
  eligibility varchar(20) DEFAULT NULL,
  application_date timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fix: prevent duplicate pending applications per student per scholarship type
ALTER TABLE scholarship_applications
ADD CONSTRAINT uq_pending_application
UNIQUE (student_id, scholarship_type, status);

INSERT INTO `students` (`student_id`, `full_name`, `email`, `password`, `course`, `year_level`, `contact_number`, `address`) 
VALUES ('2024100000', 'Juan Dela Cruz', 'juandc@gmail.com', 'juan1234', 'BS in Information Technology (BSIT)', '2nd Year', '09771234567', 'Malolos, Bulacan')
