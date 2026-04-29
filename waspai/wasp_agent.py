import pyperclip
import requests
import time

print("WASP AI AGENT ACTIVE... Monitoring system for suspicious links.")

last_url = ""

while True:
    # Get current clipboard text
    current_clipboard = pyperclip.paste()

    # If it's a new link starting with http
    if current_clipboard.startswith("http") and current_clipboard != last_url:
        print(f"Detecting link: {current_clipboard}")
        
        # Send it to your XAMPP server automatically
        try:
            requests.post("http://localhost/waspai/analyze.php", data={'url': current_clipboard})
            print("Link sent to WASP AI for background analysis.")
        except:
            print("Error: Make sure XAMPP is running!")
            
        last_url = current_clipboard
    
    time.sleep(2) # Check every 2 seconds