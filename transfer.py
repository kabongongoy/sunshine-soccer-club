"""Transfer a local file into the WordPress pod via stdin/base64."""
import subprocess, base64, sys

# Dynamically resolve the running WordPress pod name
result = subprocess.run(
    ['kubectl', 'get', 'pods', '-n', 'soccer', '-l', 'app=wordpress',
     '--field-selector=status.phase=Running',
     '-o', 'jsonpath={.items[0].metadata.name}'],
    capture_output=True, text=True
)
POD = result.stdout.strip()
if not POD:
    print('ERROR: No running WordPress pod found in namespace soccer')
    sys.exit(1)

src  = sys.argv[1]
dest = sys.argv[2]

with open(src, 'rb') as f:
    data = f.read()

proc = subprocess.run(
    ['kubectl', 'exec', '-n', 'soccer', POD, '-i', '--', 'bash', '-c',
     f'base64 -d > {dest}'],
    input=base64.b64encode(data),
    capture_output=True
)
if proc.returncode == 0:
    print(f'Transferred {len(data)} bytes to {dest} (pod: {POD})')
else:
    print('FAILED:', proc.stderr.decode())
    sys.exit(1)
