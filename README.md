<p align="center">
  <img src="https://img.shields.io/badge/VERSION-3.0-red?style=for-the-badge" />
  <img src="https://img.shields.io/badge/LICENSE-MIT-green?style=for-the-badge" />
  <img src="https://img.shields.io/badge/PHP-7.4+-blue?style=for-the-badge&logo=php" />
  <img src="https://img.shields.io/badge/BASH-5.0+-black?style=for-the-badge&logo=gnu-bash" />
  <img src="https://img.shields.io/badge/TERMUX-✔️-green?style=for-the-badge&logo=android" />
</p>

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=800&size=35&duration=3000&pause=500&color=FF0000&center=true&vCenter=true&width=700&lines=%F0%9F%94%A5+VANDAL+PHISH;%F0%9F%93%B8+Camera+Capture;%F0%9F%8E%A4+Microphone+Recording;%F0%9F%93%8D+GPS+Tracking;%F0%9F%92%BB+Device+Fingerprinting;%F0%9F%8C%90+Auto+HTTPS+Tunnel" alt="VANDAL PHISH" />
</p>

---

<div align="center">

# 🔥 VANDAL PHISH

**Phishing Assessment Toolkit**

*Capture camera, microphone, GPS, and device information through social engineering*

</div>

---

## ❓ What This Is

A **single Bash script** that sets up phishing pages to demonstrate how social engineering attacks work. When a target visits the page and interacts with it, their browser may ask for camera/microphone permission. If granted, photos and audio are captured. GPS and device information are collected automatically (if the browser allows it).

---

## ⚠️ Important Limitations

- **Camera & Microphone require user permission.** The browser will show a prompt. The target must click "Allow."
- **GPS requires permission** on most modern browsers.
- **This is not a hacking tool.** It's for security awareness training and authorized testing.
- **If the target denies permission, nothing is captured.**

---

## 📥 Installation

```bash
git clone https://github.com/vandalcyberi-droid/VANDAL-PHISH.git
cd VANDAL-PHISH
bash run.sh
```

---

## 🛠️ How To Use

```bash
bash run.sh
```

You'll be asked:
1. Which template to use (4 options)
2. Which permissions to request (5 levels)

A local PHP server starts, a public HTTPS tunnel is created, and you get a link. Send the link to your test target.

---

## 🎭 Templates

| # | Name | What The Target Sees |
|---|------|---------------------|
| 1 | 💥 SMS Bomber Pro | A fake SMS bombing website |
| 2 | 🔓 Rubika Hack Pro | A fake Rubika account hacker |
| 3 | 📸 Gallery Hack Pro | A fake gallery extractor |
| 4 | 🔐 Rubika Filter | A fake filter code generator |

All templates ask the user to click a button. After clicking, the browser permission prompt appears.

---

## 📊 What Can Be Captured

| Data | How It's Captured | User Permission Needed |
|------|-------------------|:--------------------:|
| 📸 Camera Photos | `getUserMedia()` API | Yes |
| 🎤 Microphone Audio | `getUserMedia()` API | Yes |
| 📍 GPS Location | `geolocation` API | Yes (on most browsers) |
| 📱 Device Info | `navigator` object | No |
| 🌐 IP Address | Server-side | No |
| 🖥️ Screen Resolution | `screen` object | No |

---

## 📁 Files

```
VANDAL-PHISH/
└── run.sh    # The only file you need
```

Everything else (HTML pages, PHP scripts) is generated automatically when `run.sh` executes.

---

## 🔧 Requirements

- **PHP 7.4+** (auto-installed if missing)
- **OpenSSH** (auto-installed if missing)
- **Internet connection** (for tunnel)
- **Termux** or any Linux environment

---

## 🌐 Tunnel

Uses `localhost.run` (free, no signup) to create a public HTTPS URL.

---

## 📊 Dashboard

After data is captured, view it at:

```
https://your-link.lhr.life/panel.php
Password: vandal123
```

---

## ⚠️ Legal Disclaimer

This tool is provided for **educational and authorized testing purposes only**.

- Use only on devices and accounts you **own**
- Use only with **explicit written permission** from the target
- **Unauthorized access** to someone's camera, microphone, or location is **illegal**
- The developer assumes **no liability** for misuse

---

## 👤 Author

**VANDAL CYBERI**

- GitHub: [@vandalcyberi-droid](https://github.com/vandalcyberi-droid)
- Project: [VANDAL-PHISH](https://github.com/vandalcyberi-droid/VANDAL-PHISH)

---

<div align="center">

### Made by VANDAL CYBERI

</div>