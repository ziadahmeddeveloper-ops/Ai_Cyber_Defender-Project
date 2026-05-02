# Ai_Cyber_Defender-Project
# 🛡️ AI Cyber Defender - Advanced Threat Detection Platform

**AI Cyber Defender** is a premium, full-stack cybersecurity monitoring platform that leverages Artificial Intelligence to detect, analyze, and mitigate digital threats in real-time. Designed with a modern **Glassmorphism UI**, it provides a high-performance dashboard for security professionals and organizations.

## 🚀 Key Features

*   **Real-time Threat Monitoring:** Automated live-stream of security events and system logs with instant status updates.
*   **AI-Powered Scanning:** Dual-engine scanner for:
    *   **URL Analysis:** Detects phishing, malware, and malicious domains.
    *   **Log Analysis:** Identifies brute force attacks, unauthorized access, and system anomalies.
*   **Interactive Dashboard:** High-fidelity data visualization using `Chart.js` for threat distribution and activity trends.
*   **Detailed Security Reports:** Generates historical analysis reports (Daily, Weekly, Monthly) with actionable insights.
*   **Deep-Dive Analysis:** Detailed modal views for every detected threat, including Attacker IP, Threat Scores, and Recommended Actions.
*   **Secure Authentication:** Complete user management system with persistent sessions and secure access controls.

## 🛠️ Technology Stack

*   **Frontend:** Vanilla JavaScript (ES6+), HTML5, CSS3 (Advanced Glassmorphism Design System).
*   **Backend:** PHP 8.x (RESTful API Architecture).
*   **Database:** MySQL (Relational Schema for Users, Logs, and Analytics).
*   **AI Integration:** Python Flask API (Pre-configured bridge for ML model predictions).
*   **Visualization:** Chart.js, FontAwesome 6, Google Fonts (Outfit & Inter).

## 🎨 Design Philosophy

The project adheres to a **Premium Dark Mode** aesthetic, featuring:
*   Fluid Glassmorphism components.
*   Responsive layouts for all screen sizes.
*   Micro-animations and real-time UI updates for a "living" dashboard experience.

## 📦 Installation & Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/yourusername/ai-cyber-defender.git
    ```
2.  **Database Configuration:**
    *   Import the provided SQL schema or run `api/setup_db.php`.
    *   Update `api/config.php` with your database credentials.
3.  **Server Requirements:**
    *   PHP 7.4+ or 8.x.
    *   Apache/Nginx (Recommended: XAMPP for local testing).
4.  **AI Backend (Optional):**
    *   Ensure the Python Flask API is running on `http://127.0.0.1:5000`.

## 📜 License

Distributed under the MIT License. See `LICENSE` for more information.

---
*Developed with ❤️ for a safer digital world.*
