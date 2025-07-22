# 📦 GUDANGKU

> **GudangKu Web** is a comprehensive inventory management application integrated with **GudangKu Mobile**, **Telegram Bot**, **Line Bot**, and **Desktop (Admin)**. It allows users to **manage and list inventory** items within households or warehouses, including details such as name, category, price, brand, placement, and availability. Users can also create **room floor maps** to pinpoint item locations, gain insights through **inventory analysis charts** and summaries, and group items into **reports** for shopping, maintenance, or travel purposes, which can be **exported as PDFs or Excel** files. Additionally, the system supports setting **reminders for scheduled usage**, maintenance, or other important actions, ensuring efficient and organized item management.

## 📋 Basic Information

If you want to see the project detail documentation, you can read my software documentation document. 

1. **Pitch Deck**
https://docs.google.com/presentation/d/1a0ONZzNSUXJZOvnDljwQQ3OqhJxsB8uAf82kwPc-JnQ/edit?usp=drive_link 

2. **Diagrams**
https://drive.google.com/drive/folders/1KyuiC89axkeIvLK429qSZcZzD2sYsN81?usp=drive_link 

3. **Software Requirement Specification**
https://docs.google.com/document/d/1euvEbvb6qtGIZI1JKHPmNln5ZAPjoC_ZDYajyph1HSQ/edit?usp=sharing  

4. **User Manual**
https://docs.google.com/presentation/d/1MFiRAy8cw7mfLJuFAAxUgHJP4rdCCjJwn1ZuTRrmonw/edit?usp=drive_link 

5. **Test Cases**
https://docs.google.com/spreadsheets/d/18sJ30LpBuwAWojDD1GS3zzhCvOJjA4algefdBll3Kwo/edit?usp=drive_link 

### 🌐 Deployment URL

- Web : https://gudangku.leonardhors.com 
- Backend (Swagger Docs) : https://gudangku.leonardhors.com/api/documentation#/

### 📱 Demo video on the actual device

[URL]

---

## 🎯 Product Overview
- **Inventory Management**
Users can manage and list all their inventory items in their household or warehouse. They can add item details such as name, category, price, brand, placement, and availability status.

- **Manage Placement**
Users can create a floor map of their rooms and specify the exact location of each item within those rooms.

- **Inventory Analysis**
Based on the input data, users can view various charts and summaries of their inventory, providing valuable insights for item maintenance and management.

- **Inventory Report**
Items can be grouped and included in reports that function as shopping lists, daily maintenance checklists, or travel packing lists. Reports can also be exported as PDFs or further analyzed.

- **Exported Dataset**
Users can export their data in Excel or PDF format for personal use and management.

- **Reminder**
Each item in the inventory can have reminders set, allowing users to schedule dates for usage, maintenance, or other important actions.

## 🚀 Target Users

1. **Homeowners & Renters**
Individuals or families who want to organize and manage household items, track where things are stored, schedule maintenance, and set reminders for tasks like restocking or repairs.

2. **Small Business Owners**
Owners of small shops, cafes, or home businesses who need to monitor stock levels, manage item placements, and generate reports for inventory and supply tracking.

3. **Office Administrators**
Personnel responsible for managing office supplies, equipment, and assets who benefit from reminders, placement tracking, and regular reporting.

4. **Collectors & Hobbyists**
Users with personal collections—like tech gadgets, art, or memorabilia. Who want to catalog their items, note item conditions, and receive upkeep reminders.

## 🧠 Problem to Solve

1. People often **forget where their items are placed**, especially in large households or warehouses, leading to **wasted time** and **frustration** when searching for them.  
2. It's difficult to **track the condition, value, and usage** of items over time, which causes **inefficient maintenance** and **unexpected replacements**.  
3. Without a centralized system, users often rely on **scattered notes or memory** for inventory and reminders, resulting in **disorganization**.  
4. Creating **manual reports or packing lists** for shopping, maintenance, or travel is **time-consuming** and prone to **errors**.  
5. Users lack tools to **analyze inventory trends** or **export data**, making it hard to gain **insights** or **share structured information**.

## 💡 Solution

1. Provide a way to **digitally map item placements** within rooms or warehouses, so users can quickly **locate items** using floorplans and visual markers.  
2. Let users add **detailed item metadata** such as name, category, price, brand, and condition, and schedule **reminders** for maintenance, usage, or expiry.  
3. Offer a **cross-platform solution** (Web, Mobile, Telegram, and Line) that allows users to **sync and manage data seamlessly**.  
4. Include tools to **generate and export reports** (e.g., shopping lists, maintenance checklists, packing guides) in **PDF or Excel formats**.  
5. Visualize inventory with **charts, summaries, and analytics**, helping users make **data-driven decisions** about their assets.

## 🔗 Features

- 📦 Inventory Data Management
- ⏰ Reminder & History
- 📅 Sync with Google Calendar
- 📕 Report Management
- 🤖 Telegram and Line Bot Chat Integration
- 📄 Data Export
- 📊 Analytics & Summaries

---

## 🛠️ Tech Stack

### Backend

- PHP Laravel
- PHP - Telegram Bot
- PHP - Line Bot

### Database

- MySQL

### Others Data Storage

- Firebase Storage (Cloud Storage for Asset File)
- Redis (In-Memory Storage for Stats)

### Infrastructure & Deployment

- Cpanel (Deployment)
- Github (Code Repository)
- Firebase (External Services)

### Other Tools & APIs

- Postman
- Swagger Docs

---

## 🏗️ Architecture
### Structure

### 📁 Project Structure

| Directory/File       | Purpose                                                                                   |
|----------------------|-------------------------------------------------------------------------------------------|
| `app/Exceptions/`    | Custom exception handling logic.                                                          |
| `app/Exports/`       | Data export logic, e.g., for Excel or PDF generation.                                     |
| `app/Helpers/`       | Utility / helper functions used across the app.                                             |
| `app/Http/Controllers/` | Handles incoming HTTP requests and sends responses.                                   |
| `app/Jobs/`          | Queued jobs for background processing.                                                    |
| `app/Mail/`          | Configuration or instance of email broadcast.                                                    |
| `app/Models/`        | Eloquent model definitions mapped to database tables.                                     |
| `app/Providers/`     | Service providers for bootstrapping application services.                                 |
| `app/Rules/`         | Custom form request validation rules like allowed value.                                                     |
| `app/Schedule/`      | Scheduled tasks like cron jobs using Laravel scheduler.                                   |
| `app/Service/`       | External service function like data handling using Firebase and Google Calendar service.                                   |
| `config/`            | Configuration files for services, database, cache, auth, and constants.                   |
| `database/factories/`| Define what kind of data for dummy.                                                 |
| `database/migrations/`| Defines database template.                                                         |
| `database/seeders/`  | Seeds database with default or dummy data.                                              |
| `firebase/`          | Service account JSON.                                                    |
| `public/`            | Publicly accessible folder, serves as the document root for web servers.                  |
| `resources/`         | Views, language files, and other frontend resources.                                      |
| `routes/`            | API routes / endpoints. |
| `storage/`           | File uploads. |
| `tests/`             | Feature and unit tests.                                                                   |
| `tests_reports/`     | Test report outputs.                                           |
| `vendor/`            | Composer-managed PHP dependencies.                                                        |
| `.env`               | Environment-specific variables.                                                           |
| `.env.example`       | Example environment configuration file.                                                   |
| `.gitignore`         | Specifies files and folders to be ignored by Git.                                         |                                             |

---

### 🧾 Environment Variables

To set up the environment variables, just create the `.env` file in the root level directory.

| Variable Name                        | Description                                                              |
|----------------------------------|--------------------------------------------------------------------------|
| `DB_CONNECTION`                  | Database driver/connection (e.g., `mysql`, `pgsql`)                      |
| `DB_HOST`                        | Database host (e.g., `localhost`)                                        |
| `DB_PORT`                        | Database port (e.g., `3306`)                                             |
| `DB_USER`                        | Database username                                                        |
| `DB_PASSWORD`                    | Database password                                                        |
| `DB_DATABASE`                    | Name of the primary database                                             |
| `TEST_DB_HOST`                   | Host for the test database                                               |
| `TEST_DB_PORT`                   | Port for the test database                                               |
| `TEST_DB_USER`                   | Username for the test database                                           |
| `TEST_DB_PASSWORD`               | Password for the test database                                           |
| `TEST_DB_NAME`                   | Name of the test database                                                |
| `FIREBASE_BUCKET_NAME`           | Firebase Storage bucket name for handling file uploads                   |
| `GOOGLE_APPLICATION_CREDENTIALS`| Path to Firebase service account JSON file                               |
| `TELEGRAM_BOT_TOKEN`             | Telegram bot token for chat integration                                  |
| `LINE_BOT_TOKEN`                 | Line bot token for chat integration                                      |
| `MAIL_MAILER`                    | Mail transport method (e.g., `smtp`)                                     |
| `MAIL_HOST`                      | Mail server host (e.g., `smtp.mailtrap.io`)                              |
| `MAIL_PORT`                      | Mail server port (e.g., `587`)                                           |
| `MAIL_USERNAME`                  | Mail server username                                                     |
| `MAIL_PASSWORD`                  | Mail server password                                                     |
| `MAIL_FROM_ADDRESS`              | Default email address to send from                                       |
| `MAIL_ENCRYPTION`                | Encryption protocol (e.g., `tls`, `ssl`)                                 |
| `MAIL_FROM_NAME`                 | Name that appears in sent emails                                         |
| `GOOGLE_CLIENT_ID`               | The client ID obtained from Google Cloud Console for OAuth authentication. |
| `GOOGLE_CLIENT_SECRET`           | The client secret associated with the above client ID.                      |
| `GOOGLE_REDIRECT_URI`            | The authorized redirect URI where Google sends users after authentication. |                                 |


---

## 🗓️ Development Process

### Technical Challenges

- **Daily Limitation** for data transaction in Firebase Storage
- Not all **utils (helpers)** can be tested in **automation testing**
- Feature that return the **output in Telegram / Line Chat or Exported File** must be **tested manually** 

---

## 🚀 Setup & Installation

### Prerequisites

#### 🔧 General
- Git installed
- A GitHub account
- Basic knowledge of PHP, Software Testing, Firebase Service, and SQL Databases
- Code Editor
- Telegram Account
- Line Account
- Postman
- Google Console Account

#### 🧠 Backend
- PHP version 8.1 or higher
- Composer version 2.8 or higher
- Git for cloning the repository.
- MySQL database.
- Make (optional), if your project includes a Makefile to simplify common commands.
- Firebase service account JSON file or Google App Credential.
- Telegram Bot token, you can get it from **Bot Father** `@BotFather`
- Telegram User ID for testing the scheduler chat in your Telegram Account. You can get it from **IDBot** `@username_to_id_bot`
- Line Bot token, you can get it from **Line Developer Console** 
- Line User ID for testing the scheduler chat in your Line Account. You can get it from webhook events
- Internet access from the hosting server (for Telegram webhook polling or long-polling)

### Installation Steps

**Local Init & Run**
1. Download this Codebase as ZIP or Clone to your Git
2. Set Up Environment Variables `.env` at the root level directory. You can see all the variable name to prepare at the **Project Structure** before or `.env.example`
3. Install Dependencies using `composer install`
4. **Database Migration** will run if you execute the command `php artisan migrate`.
5. **Seeders** will run if you execute `php artisan db:seed`.
6. **Task Scheduler** in Laravel is managed via `php artisan schedule:run`, usually triggered by a system cron job.
7. **Queue** for background process will run after you execute `php artisan queue:work`.
8. **Run the Laravel** using `php artisan serve`

**CPanel Deployment**
1. Source code uploaded to CPanel
2. Prepare the `.htaccess` in root directory
3. ...

---

## 👥 Team Information

| Role     | Name                    | GitHub                                     | Responsibility |
| -------- | ----------------------- | ------------------------------------------ | -------------- |
| Backend Developer  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Backend and Telegram Bot Codebase         |
| Frontend Developer  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Frontend Codebase         |
| Mobile Developer  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Mobile Codebase         |
| System Analyst  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Diagram & Software Docs         |
| Quality Assurance  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Testing & Documented The API         |

---

## 📝 Notes & Limitations

### ⚠️ Precautions When Using the Service
- Ensure API endpoints requiring authentication are protected with proper middleware.
- Do not expose sensitive environment variables (e.g., API keys, database credentials) in public repositories.
- Avoid using seeded dummy data in production environments.
- Avoid using seeded dummy data with large seed at the same time.

### 🧱 Technical Limitations
- Telegram & Line bot polling may cause delays or downtime if the server experiences high load

### 🐞 Known Issues
- Limitation when using Firebase Storage for free plan Firebase Service, upgrade to Blaze Plan to use more.

---

## 🏆 Appeal Points

- 📦 **Comprehensive Inventory Management**: Effortlessly manage household or warehouse items with details like name, category, price, brand, and availability.
- 🗺️ **Precise Placement Mapping**: Create room floor maps and mark exact item locations to streamline searching and organizing.
- 🤖 **Multi-Bot Integration**: Integrated with Telegram, and Line bots for real-time reminders or inventory updates.
- 📊 **Insightful Reports & Analysis**: View visual inventory summaries and generate PDF/Excel reports for audits, packing lists, or shopping plans.
- 🔔 **Smart Reminders**: Schedule reminders for maintenance, usage, or restocking to ensure proactive item management.
- 🧱 **Built with Laravel**: Clean, modular, and testable code using Laravel’s MVC structure, making it robust and easy to extend.

---

_Made with ❤️ by Leonardho R. Sitanggang_