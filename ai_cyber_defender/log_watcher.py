import time
import requests
import os
import json
import threading

# Path to the log file being monitored
LOG_FILE = r"C:\xampp\htdocs\New folder\AI-Cyber-Defender-main (1)\AI-Cyber-Defender-main\AI_Cyber_Defender_Test_Logs.txt"
# Local PHP API Endpoint
API_ENDPOINT = "http://localhost:8000/api/ai.php"

def follow(thefile):
    """Generator function that yields new lines in a file as they are added."""
    thefile.seek(0, 2)  # Go to the end of the file
    while True:
        line = thefile.readline()
        if not line:
            time.sleep(0.5)  # Sleep briefly
            continue
        yield line

def watch_logs():
    print(f"[*] Starting Real-time Log Watcher...")
    print(f"[*] Monitoring: {LOG_FILE}")
    print(f"[*] Sending alerts to: {API_ENDPOINT}")
    print("-" * 50)
    
    if not os.path.exists(LOG_FILE):
        print("[!] Log file does not exist. Creating an empty one.")
        with open(LOG_FILE, 'w') as f:
            pass
            
    with open(LOG_FILE, "r") as logfile:
        loglines = follow(logfile)
        for line in loglines:
            line = line.strip()
            if line:
                print(f"[+] New log detected: {line[:60]}...")
                payload = {
                    "scan_target": line,
                    "scan_type": "log"
                }
                try:
                    # Send to the PHP backend, which forwards to Python AI and saves to DB
                    response = requests.post(API_ENDPOINT, json=payload, timeout=10)
                    if response.status_code == 200:
                        data = response.json()
                        result = data.get("data", {})
                        prediction = result.get("prediction", "N/A")
                        attack_type = result.get("attack_type", "N/A")
                        
                        if prediction in ['anomaly', 'malicious']:
                            print(f"    [🚨 THREAT DETECTED] {attack_type}")
                        else:
                            print(f"    [✅ SECURE] Normal Activity")
                    else:
                        print(f"    [!] API Error: {response.status_code} - {response.text}")
                except Exception as e:
                    print(f"    [!] Connection Error: {str(e)}")

if __name__ == '__main__':
    try:
        watch_logs()
    except KeyboardInterrupt:
        print("\n[*] Stopping Log Watcher.")
