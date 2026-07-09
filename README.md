# servgo
Full-stack home services booking platform built with PHP &amp; MySQL
# SERVGO – Home Services Booking Platform

SERVGO is a full-stack web application that connects customers with verified local
service providers for home services such as plumbing, electrical repairs, cleaning,
painting, carpentry, and AC repair. It was built as a personal project to apply and
strengthen my skills in web development, database design, and system architecture
alongside my studies in Network Engineering.



## Features

### For Customers
- Browse and search available services by category
- Book a service by selecting date, time, and location
- Make online payments and receive digital receipts
- Leave reviews and ratings for completed services
- Manage bookings from a personal dashboard

### For Service Providers
- Create and manage service listings
- Accept, update, and track bookings
- View earnings and payment history
- Manage provider profile

### For Admin
- Manage all users, providers, and bookings from a central dashboard
- Approve/monitor service listings
- View and moderate customer reviews
- Oversee platform activity

---

## Tech Stack

| Layer          | Technology              |
|----------------|--------------------------|
| Backend        | PHP (procedural)         |
| Database       | MySQL                    |
| Frontend       | HTML, CSS, JavaScript    |
| Email          | PHP Mailer (custom)      |

---

## Project Structure

servgo/
├── admin/          # Admin dashboard: bookings, providers, reviews, services, users
├── assets/         # CSS and JavaScript
├── config/         # Database connection config
├── includes/       # Shared header, footer, mailer
├── provider/       # Provider dashboard: services, bookings, earnings, profile
├── user/           # Customer dashboard: bookings, payments, reviews, profile
├── index.php       # Homepage
├── login.php
├── register.php
├── about.php
└── contact.php

---


## What I Learned
This was my first time building a complete multi-role system from scratch. 
The hardest part was getting the database relationships right between users, 
providers, bookings, and payments — I had to redesign the schema a few times 
before it worked cleanly. I also learned how to properly separate access levels 
so admins, providers, and customers each only see what they should.

---

## Author

**Jeyaseelan Anojh**
Aspiring Network Engineer | HNDIT (SLIATE)
📧 anojh.it@gmail.com
🔗 [LinkedIn](https://www.linkedin.com/in/jeyaseelan-anojh-a882a0419/)
