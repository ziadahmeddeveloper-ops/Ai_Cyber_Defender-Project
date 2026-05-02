import time
import requests
from pathlib import Path

BACKEND_URL = "http://localhost:8080/New%20folder/ai_cyber_defender/api/ingest-logs.php"
LOG_FILE = "live_logs.txt"

def follow_file(path):
    path = Path(path)
    path.touch(exist_ok=True)

    with open(path, "r", encoding="utf-8") as f:
        f.seek(0, 2)

        while True:
            line = f.readline()

            if not line:
                time.sleep(1)
                continue

            log = line.strip()

            if not log:
                continue

            payload = {
                "source": "windows",
                "logs": [log],
                "user_id": 2
            }

            try:
                res = requests.post(BACKEND_URL, json=payload, timeout=5)
                print("Sent:", log)
                print("Response:", res.status_code, res.text)
            except Exception as e:
                print("Failed to send log:", e)

if __name__ == "__main__":
    print("Live log agent started...")
    print(f"Monitoring: {LOG_FILE}")
    follow_file(LOG_FILE)
