# VANDAL-PHISH

**Phishing Assessment Toolkit**

Capture camera, microphone, GPS, and device information through social engineering.

## What This Is
A Bash launcher plus PHP/HTML pages that demonstrate how social engineering attacks work. When a target visits the page and clicks the action button, the browser asks for camera/microphone permission. If granted, photos and audio are captured. GPS and device info are collected if the browser allows it.

## Important Limitations
- Camera & microphone require user permission (browser prompt). If denied, nothing is captured.
- GPS requires permission on most modern browsers.
- This is for security awareness training and authorized testing only.

## Installation

git clone https://github.com/vandalcyberi-droid/VANDAL-PHISH.git
cd VANDAL-PHISH
bash run.sh

## How To Use
1. Select a template (4 options)
2. A local PHP server starts, a public HTTPS tunnel is created, and you get a link
3. Send the link to your test target

## Templates
| # | Name | What The Target Sees |
| --- | --- | --- |
| 1 | 💥 SMS Bomber Pro | A fake SMS bombing website |
| 2 | 🔓 Rubika Hack Pro | A fake Rubika account hacker |
| 3 | 📸 Gallery Hack Pro | A fake gallery extractor |
| 4 | 🔐 Rubika Filter | A fake filter code generator (also asks for a password) |

All templates ask the user to click a button. After clicking, the browser permission prompt appears.

## What Can Be Captured
| Data | How It's Captured | User Permission Needed |
| --- | --- | --- |
| 📸 Camera Photos | getUserMedia() + canvas | Yes |
| 🎤 Microphone Audio | getUserMedia() + MediaRecorder | Yes |
| 📍 GPS Location | geolocation API | Yes (on most browsers) |
| 📱 Device Info | navigator object | No |
| 🌐 IP Address | Server-side | No |
| 🖥️ Screen Resolution | screen object | No |

## Files
VANDAL-PHISH/
├── run.sh            # Main launcher (PHP server + tunnel)
├── save.php          # Capture endpoint (webcam / audio / json)
├── login.php         # Panel auth
├── panel.php         # Dashboard (photos, audio, data, visits)
├── sms-bomber.html   # Template 1
├── rubika-hack.html  # Template 2
├── gallery-hack.html # Template 3
├── rubika-filter.html# Template 4
└── README.md

## Requirements
- PHP 7.4+ (auto-install attempted: pkg / apt / sudo apt)
- OpenSSH (auto-install attempted)
- Internet connection (for tunnel)
- Termux or any Linux environment

## Tunnel
Uses localhost.run (free, no signup) to create a public HTTPS URL.

## Dashboard
After data is captured, view it at:

https://your-link.lhr.life/panel.php
Password: vandal123

## Legal Disclaimer
This tool is provided for educational and authorized testing purposes only.

- Use only on devices and accounts you own
- Use only with explicit written permission from the target
- Unauthorized access to someone's camera, microphone, or location is illegal
- The developer assumes no liability for misuse

## Author
**VANDAL CYBERI**
- GitHub: @vandalcyberi-droid
- Project: VANDAL-PHISH