import threading
import subprocess
import time

def run_script1():
    subprocess.run(["python", "upload_sheet.py"])

def run_script2():
    subprocess.run(["python", "recup_tel.py"])

run_script1()
run_script2()

