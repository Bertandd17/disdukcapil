import requests
import argparse
import threading
import queue
import os
from datetime import datetime
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

LOG_DIR = 'logs'
LOG_FILE = os.path.join(LOG_DIR, 'scan.log')
SUCCESS_FILE = 'success.txt'

cyan_color = '\033[96m'
light_orange_color = '\033[38;5;214m'
reset_color = '\033[0m'

def banner():
    print(r"""
  ______     _______     ____   ___ ____  _  _        _____  __   ___   ___  _
 / ___\ \   / | ____|   |___ \ / _ |___ \| || |      |___ / / /_ / _ \ / _ \/ |
| |    \ \ / /|  _| _____ __) | | | |__) | || |_ _____ |_ \| '_ | (_) | (_) | |
| |___  \ V / | |__|_____/ __/| |_| / __/|__   _|________) | (_) \__, |\__, | |
 \____|  \_/  |_____|   |_____|\___|_____|  |_|      |____/ \___/  /_/   /_/|_|

-> POC CVE Scanner for various vulnerabilities.
-> Use Wisely.
""")

def create_log_dir():
    if not os.path.exists(LOG_DIR):
        os.makedirs(LOG_DIR)
        print_message('info', f"Log directory created: {LOG_DIR}")

def log_message(message):
    with open(LOG_FILE, 'a') as log_file:
        log_file.write(f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')} - {message}\n")

def success_message(message):
    with open(SUCCESS_FILE, 'a') as success_file:
        success_file.write(f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')} - {message}\n")

def print_message(level, message):
    colors = {
        'info': '\033[90m',
        'success': '\033[92m',
        'warning': '\033[33;1m',
        'error': '\033[31m',
        'vulnerable': '\033[96m'
    }
    reset_color = '\033[0m'
    print(f"{colors[level]}[{level.upper()}] {message}{reset_color}")
    log_message(message)

def make_request(url):
    try:
        response = requests.get(url, verify=False)
        return response.text, response.status_code
    except requests.RequestException as e:
        return None, None

def test_sql_injection(url):
    payload = "'"
    full_url = f"{url}?id={payload}"
    body, status = make_request(full_url)

    # Check for actual database error keywords indicating error-based SQLi
    sql_errors = [
        "sql syntax", "mysql_fetch", "mariadb", "syntax error",
        "you have an error in your sql syntax", "unclosed quotation mark",
        "database error", "driverexception", "postgresql query failed",
        "sqlstate"
    ]
    if body:
        for error in sql_errors:
            if error in body.lower():
                success_message(f"SQL Injection Vulnerable: {url}")
                return True
    return False

def test_xss(url):
    payload = "<script>alert('XSS')</script>"
    full_url = f"{url}?q={payload}"
    body, status = make_request(full_url)
    if body and payload in body:
        success_message(f"XSS Vulnerable: {url}")
        return True
    return False

def test_path_traversal(url):
    payload = "/../../../../etc/passwd"
    full_url = f"{url}{payload}"
    body, status = make_request(full_url)
    if body and "root:x" in body:
        success_message(f"Path Traversal Vulnerable: {url}")
        return True
    return False

def test_directory_listing(url):
    body, status = make_request(url)
    if body and ("Index of /" in body or "Directory listing for" in body):
        success_message(f"Directory Listing Enabled: {url}")
        return True
    return False

def test_command_injection(url):
    payload = "; echo VULN_COMMAND_INJECTION_TEST"
    full_url = f"{url}?cmd={payload}"
    body, status = make_request(full_url)
    if body and "VULN_COMMAND_INJECTION_TEST" in body:
        success_message(f"Command Injection Vulnerable: {url}")
        return True
    return False

def test_lfi(url):
    payload = "/etc/passwd"
    full_url = f"{url}?file={payload}"
    body, status = make_request(full_url)
    if body and "root:x" in body:
        success_message(f"LFI Vulnerable: {url}")
        return True
    return False

def test_rfi(url):
    payload = "http://example.com"
    full_url = f"{url}?file={payload}"
    body, status = make_request(full_url)
    if body and "Example Domain" in body:
        success_message(f"RFI Vulnerable: {url}")
        return True
    return False

def test_file_upload(url):
    files = {'file': ('test.txt', 'This is a test file')}
    try:
        response = requests.post(url, files=files, verify=False)
        if response.status_code == 200 and "file uploaded" in response.text.lower():
            success_message(f"File Upload Vulnerable: {url}")
            return True
    except requests.RequestException as e:
        return False
    return False

def test_open_redirect(url):
    payload = "/redirect?url=http://malicious.com"
    full_url = f"{url}{payload}"
    try:
        response = requests.get(full_url, allow_redirects=False, verify=False)
        if response.status_code in [301, 302] and "location" in response.headers and "malicious.com" in response.headers["location"]:
            success_message(f"Open Redirect Vulnerable: {url}")
            return True
    except requests.RequestException as e:
        return False
    return False

def test_csrf(url):
    payload = "<html><body><form action=\"{}\" method=\"POST\"><input type=\"hidden\" name=\"csrf\" value=\"\"><input type=\"submit\"></form></body></html>".format(url)
    headers = {'Content-Type': 'text/html'}
    try:
        response = requests.post(url, data=payload, headers=headers, verify=False)
        if response.status_code == 200 and "csrf token missing" not in response.text.lower():
            success_message(f"CSRF Vulnerable: {url}")
            return True
    except requests.RequestException as e :
        return False
    return False

def test_cves(url):
    if test_sql_injection(url):
        print_message('vulnerable', f"SQL Injection Vulnerable: {url}")
    else:
        print_message('info', f"SQL Injection Not Vulnerable: {url}")

    if test_xss(url):
        print_message('vulnerable', f"XSS Vulnerable: {url}")
    else:
        print_message('info', f"XSS Not Vulnerable: {url}")

    if test_path_traversal(url):
        print_message('vulnerable', f"Path Traversal Vulnerable: {url}")
    else:
        print_message('info', f"Path Traversal Not Vulnerable: {url}")

    if test_directory_listing(url):
        print_message('vulnerable', f"Directory Listing Enabled: {url}")
    else:
        print_message('info', f"Directory Listing Not Enabled: {url}")

    if test_command_injection(url):
        print_message('vulnerable', f"Command Injection Vulnerable: {url}")
    else:
        print_message('info', f"Command Injection Not Vulnerable: {url}")

    if test_lfi(url):
        print_message('vulnerable', f"LFI Vulnerable: {url}")
    else:
        print_message('info', f"LFI Not Vulnerable: {url}")

    if test_rfi(url):
        print_message('vulnerable', f"RFI Vulnerable: {url}")
    else:
        print_message('info', f"RFI Not Vulnerable: {url}")

    if test_file_upload(url):
        print_message('vulnerable', f"File Upload Vulnerable: {url}")
    else:
        print_message('info', f"File Upload Not Vulnerable: {url}")

    if test_open_redirect(url):
        print_message('vulnerable', f"Open Redirect Vulnerable: {url}")
    else:
        print_message('info', f"Open Redirect Not Vulnerable: {url}")

    if test_csrf(url):
        print_message('vulnerable', f"CSRF Vulnerable: {url}")
    else:
        print_message('info', f"CSRF Not Vulnerable: {url}")

def worker(queue):
    while not queue.empty():
        url = queue.get()
        print_message('info', f"Testing {url}")
        test_cves(url)
        queue.task_done()

def main():
    banner()
    parser = argparse.ArgumentParser(description='CVE Scanner for various vulnerabilities.')
    parser.add_argument('-f', '--file', help='File containing list of URLs (one per line)', required=True)

    args = parser.parse_args()

    create_log_dir()

    if not args.file:
        args.file = input("Please provide the path to the file containing the list of URLs: ")

    with open(args.file, 'r') as f:
        urls = [line.strip() for line in f if line.strip()]

    print_message('info', f"Testing multiple targets from file: {args.file}")

    url_queue = queue.Queue()
    for url in urls:
        url_queue.put(url)

    threads = []
    for _ in range(10):
        t = threading.Thread(target=worker, args=(url_queue,))
        t.start()
        threads.append(t)

    for t in threads:
        t.join()

    print_message('info', "Scanning complete.")

if __name__ == '__main__':
    main()

