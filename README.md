# Assetify - Office Asset Management System

A full-stack web application for managing office assets across an organisation. Built with Laravel 12, Inertia.js, and Vue 3, it provides two distinct interfaces — one for administrators and one for employees — covering the full lifecycle of an asset from creation to assignment, maintenance, and disposal.

---

## Tech Stack

**Backend**

| Package | Version |
|---|---|
| inertiajs/inertia-laravel | ^2.0 |
| spatie/laravel-permission | ^7.2 |
| maatwebsite/excel | ^3.1 |
| tightenco/ziggy | ^2.0 |

**Frontend**

| Package | Version |
|---|---|
| Tailwind CSS | ^3.4 |
| Font Awesome (Vue) | ^3.1.3 |
| Chart.js | ^4.5.1 |
| vue-chartjs | ^5.3.3 |
| vue3-toastify | ^0.2.9 |

---

## Requirements

- PHP 8.2 or higher with the following extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Composer 2.x
- Node.js 18 or higher and npm
- MySQL 8.0 or higher (or MariaDB 10.6+)
- A mail provider account 

---

## Installation

Clone the repository and move into the project directory.

```bash
git clone https://github.com/Swayam-Ghimire/Office-Asset-Management-System.git
cd Office-Asset-Management-System
```

Install PHP dependencies.

```bash
composer install
```

Install JavaScript dependencies.

```bash
npm install
```

Copy the environment file and generate the application key.

```bash
cp .env.example .env
php artisan key:generate
```

---

## Environment Configuration

Open `.env` and update the following sections before running migrations.

**Database**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=assetify
DB_USERNAME=root
DB_PASSWORD=
```

**Mail**

To use standard SMTP instead (such as Mailtrap for local development):

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

**Queue**

Notifications and emails are dispatched through the queue. Set the driver to `database` so jobs are stored in MySQL.

```env
QUEUE_CONNECTION=database
```

Run the queue tables migration after changing this setting.

```bash
php artisan queue:table
php artisan migrate
```

---

## Running the Application

Start services individually:

```bash
php artisan serve
npm run dev
php artisan queue:work
```

The application will be available at `http://127.0.0.1:8000`.

After running migrations, create the initial admin account using the seeder artisan command.

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=DepartmentSeeder
php artisan create:admin

```


---

## Queue Worker

Outgoing notifications and emails are queued jobs. The queue worker must be running for them to be delivered.

```bash
php artisan queue:work
```

---

## Roles

**Admin**, **Employee**

---

## Core Features

### Asset Management

Assets represent individual physical items in the organisation's inventory. Each asset stores a model name, category, brand, purchase date, condition (new, good, damaged), and status (available, not available, under maintenance).

Admins can create, edit, and soft-delete assets. Soft-deleted assets move to a trash view where they can be restored or permanently deleted. Asset images can be uploaded and stored in the public disk.

Employees and Admin can see all assets. But only admin can access their history.

An activity log is recorded on every significant state change (created, updated, approved, rejected, returned, maintenance reported, maintenance resolved, restored, deleted).

### Request System

Employees browse the asset inventory and submit a request for any available asset, with an optional reason. The system prevents duplicate pending requests for the same asset and uses a database transaction to atomically mark the asset as not available and create the request record, preventing race conditions when two employees request the same asset simultaneously.

Admins review pending requests and approve or reject them. Approving a request creates an assignment record. Rejecting a request returns the asset to available status.

### Assignments

An assignment record is created when a request is approved. It tracks which employee holds which asset, the assignment date, and optionally the return date.

Employees can return assets from their dashboard or from the assignments list. When an asset is returned, the system checks for any open maintenance reports on that asset. If one exists, the asset status is set to under maintenance rather than available, ensuring it is not re-assigned before being serviced.

Admins see all assignments. Employees see only their own.

### Maintenance Workflow

The maintenance system follows a defined status progression: reported, in progress, resolved.

An employee reports an issue on an asset they currently hold. The report includes a text description. The asset is immediately flagged as under maintenance. All admins receive a notification and an email alerting them to the new report.

An admin acknowledges the report by moving it to in progress. This requires the asset to have been returned first — the system blocks the transition if the asset is still assigned. Moving to in progress sets the asset condition to damaged.

An admin resolves the report by selecting the final condition of the repaired asset and optionally writing a resolution note. On resolution, the asset is set back to available with the updated condition.

Admins can also send a return request to the employee currently holding an asset, prompting them to return it so maintenance can begin. This sends both a database notification and an email with an optional admin note explaining the urgency.

No maintenance records are deleted. The full history is preserved and visible in the asset detail view under the maintenance tab.

### Notifications and Email

The system sends notifications through two channels simultaneously: database (shown in the in-app bell icon) and email (dispatched via the queue).

The following events trigger notifications:

| Event | Recipients |
|---|---|
| Request approved | Requesting employee |
| Request rejected | Requesting employee |
| Maintenance issue reported | All admins |
| Return requested by admin | Employee holding the asset |
| Asset returned | All admins |

Notifications are displayed in a dropdown bell menu in the top navigation bar. Unread notifications show a count badge. Individual notifications can be marked as read, or all can be cleared at once. A dedicated notifications page shows the full paginated history with timestamps and links to relevant assets.

### User Management

Admins create employee accounts by providing a name, email, department, role, and status. Credentials is sent to the new user's email automatically.

Admins can edit user details, change roles, activate or deactivate accounts, and delete accounts. An admin cannot delete their own account. The user profile page shows the employee's current assignments and request history.

### Categories and Departments

Categories classify assets (Laptop, Monitor, Chair, and so on). Departments group employees. Both are managed from dedicated admin pages with inline create, edit, and delete. A category cannot be deleted if it has assets assigned to it. A department cannot be deleted if it has employees in it.

### Dashboard and Charts

The admin dashboard shows six summary tiles: total assets, available, assigned, under maintenance, pending requests, and total users. Below the tiles, three charts are rendered using Chart.js via vue-chartjs.

The bar chart shows asset count per category. The doughnut chart shows the distribution of assets across the three statuses with a total count in the centre. The line chart shows the volume of requests submitted per month across the last six months, filling in any months with zero activity so the chart always covers a full six-month window.

The dashboard also shows the five most recent pending requests with inline approve and reject buttons, and a table of the ten most recent assignments.

The employee dashboard shows their own stats, a list of currently assigned assets with options to return or report an issue, and their recent request history.

### Asset Upload
Asset can be uploaded in bulk quanitity using excel file (xlsx, xlx, csv)

---
