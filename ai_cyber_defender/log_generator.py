import time
import random
from datetime import datetime
import os

LOG_FILE = "live_logs.txt"

def generate_random_ip():
    return f"{random.randint(1,255)}.{random.randint(0,255)}.{random.randint(0,255)}.{random.randint(1,255)}"

def get_safe_log():
    users = ["Ahmed", "Sarah", "Youssef", "John", "Mike", "Employee_01", "Developer_X", "System", "Cron"]
    
    scenarios = [
        # Windows Auth
        lambda: f"EventID=4624 Login success User={random.choice(users)} SRC={generate_random_ip()}",
        lambda: f"EventID=4634 Logoff User={random.choice(users)}",
        lambda: f"EventID=4672 Special privileges assigned User={random.choice(users)}",
        # Web Logs
        lambda: f"Apache - {generate_random_ip()} - [GET /index.php HTTP/1.1] 200 OK",
        lambda: f"Nginx - {generate_random_ip()} - [GET /assets/logo.png HTTP/1.1] 200 OK",
        # SSH
        lambda: f"sshd: Connection from {generate_random_ip()} port {random.randint(1024, 65535)}",
        lambda: f"sshd: Accepted publickey for {random.choice(users)} from {generate_random_ip()}",
        # System
        lambda: f"System: Service 'Windows Update' started successfully.",
        lambda: f"Cron: Job 'backup.sh' completed successfully for user {random.choice(users)}."
    ]
    
    return random.choice(scenarios)()

def get_attack_log():
    users = ["admin", "root", "administrator", "Hacker", "Backdoor", "guest", "test", "webmaster"]
    ips = [generate_random_ip() for _ in range(3)]
    
    scenarios = [
        # Windows Attacks
        lambda: f"EventID=4625 Failed login User={random.choice(users)} SRC={random.choice(ips)}",
        lambda: f"EventID=1102 The audit log was cleared User=Administrator",
        lambda: f"EventID=4720 A user account was created User=Backdoor_Admin",
        lambda: f"EventID=4688 A new process has been created Process=mimikatz.exe User=SYSTEM",
        lambda: f"EventID=5140 A network share object was accessed Share=\\\\*\\IPC$ SRC={random.choice(ips)}",
        # Web Attacks
        lambda: f"Web: SQL Injection attempt detected: GET /product.php?id=1' OR '1'='1' from {random.choice(ips)}",
        lambda: f"Web: XSS attempt detected: GET /search?q=<script>alert(1)</script> from {random.choice(ips)}",
        lambda: f"Web: Path Traversal attempt: GET /../../etc/passwd from {random.choice(ips)}",
        # SSH / Brute Force
        lambda: f"sshd: Failed password for invalid user {random.choice(users)} from {random.choice(ips)}",
        lambda: f"sshd: Failed password for root from {random.choice(ips)}",
        # Database
        lambda: f"MySQL: Failed login for 'root'@'{random.choice(ips)}'",
        lambda: f"MySQL: Suspicious command detected: 'DROP TABLE users; --' from {random.choice(ips)}"
    ]
    
    return random.choice(scenarios)()

def generate_logs():
    print(f"[*] Started ALL-TYPE Log Generator. Writing to {LOG_FILE}...")
    while True:
        # 60% chance of safe log, 40% chance of attack log to make it more exciting
        if random.random() < 0.6:
            log_msg = get_safe_log()
        else:
            log_msg = get_attack_log()
            
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        full_log = f"{timestamp} {log_msg}"
        
        with open(LOG_FILE, "a", encoding="utf-8") as f:
            f.write(full_log + "\n")
            
        print(f"[+] Generated: {full_log}")
        
        # Faster generation for more "action"
        sleep_time = random.randint(1, 4)
        time.sleep(sleep_time)

if __name__ == "__main__":
    generate_logs()
