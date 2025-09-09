Flexi File Converter

A powerful, user-friendly web application for converting files between various formats with ease. Built with PHP, Tailwind CSS, and LibreOffice for backend conversion.

Features
- Convert documents, spreadsheets, presentations, and images
- Support for multiple input and output formats
- User authentication and conversion history
- Admin dashboard for user and conversion management
- Responsive design with glassmorphism UI
- Secure file processing with no permanent storage

Supported Formats
Category       | Input Formats        | Output Formats
------------------------------------------------------------
Documents      | PDF, DOC, DOCX       | PDF, DOCX, ODT, RTF, TXT, HTML, EPUB
Spreadsheets   | XLS, XLSX, ODS, CSV | PDF, XLSX, ODS, CSV, HTML
Presentations  | PPT, PPTX, ODP      | PDF, PPTX, ODP, PNG, JPG
Images         | PNG, JPG, JPEG      | PDF, PNG, JPG

Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB
- LibreOffice (for file conversion)
- Web server (Apache/Nginx)

Installation Steps
1. Clone or Download the Project
   Place the files in your web server's directory (htdocs for XAMPP, www for WAMP).

2. Database Setup
   - Create a MySQL database named file_converter
   - Import the provided SQL structure (users, file_history, admin_stats).

3. Configure Database Connection
   Edit config.php with your database credentials.

4. Install LibreOffice
   - Windows: Download from official website, install, add to PATH
   - macOS: brew install --cask libreoffice
   - Linux: sudo apt install libreoffice

5. Verify LibreOffice Installation
   Run: soffice --version

6. Directory Permissions
   Create 'storage' and 'storage/jobs' directories and set proper permissions.

7. Test the Application
   Start web server & DB, open http://localhost/file_converter, register/login, and test conversions.

Troubleshooting
1. LibreOffice not found → Add to PATH
2. Conversion failures → Check supported formats and permissions
3. DB connection errors → Check config.php credentials
4. File upload size limits → Update php.ini settings

Debug Mode
Enable error reporting in PHP files:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

Security Notes
- Change default DB credentials
- Enable SSL/TLS
- Secure file permissions
- Keep LibreOffice & PHP updated
- Implement rate limiting
- Validate & sanitize inputs

License
This project is for educational/demonstration purposes.