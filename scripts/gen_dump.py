import os
import subprocess
import sys

DB = os.environ.get("DB_NAME", "digital_store")
USER = os.environ.get("DB_USER", "devin")
PWD = os.environ.get("DB_PASS", "devin")
HOST = os.environ.get("DB_HOST", "127.0.0.1")
OUT = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "dump.sql")

cmd = [
    "mysqldump",
    f"-h{HOST}",
    f"-u{USER}",
    f"-p{PWD}",
    "--skip-comments",
    "--hex-blob",
    "--add-drop-database",
    "--databases",
    DB,
]

with open(OUT, "w", encoding="utf-8") as f:
    r = subprocess.run(cmd, stdout=f, stderr=subprocess.PIPE)
if r.returncode != 0:
    sys.stderr.write(r.stderr.decode("utf-8", errors="replace"))
    sys.exit(r.returncode)
print(f"dump.sql written to {OUT}")
