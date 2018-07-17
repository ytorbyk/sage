
## Sage

Sage is a development environment for macOS High Sierra.


## Installation

1. Since Sage depends on Brew. Install or update [Homebrew](https://brew.sh/) to the latest version using brew update.
```bash
# if not installed
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"

# add the next line into your ~/.bash_profile or ~/.bashrc file (create if not exists)

export PATH="/usr/local/bin:/usr/local/sbin:$PATH"


# if installed

brew update
brew upgrade
```

2. Download phar package from [the latest release](https://github.com/ytorbyk/sage/releases/latest) and put it in `/usr/local/bin` folder. So it will be accessible in Terminal everywhere
```bash
curl -L https://github.com/ytorbyk/sage/releases/download/0.2.1/sage.phar > /usr/local/bin/sage
chmod +x /usr/local/bin/sage
```

3. Create home folder and generate default configuration
```bash
# It creates ~/.sage home folder and configuration in it ~/.sage/config.php.
# You can customize it before next step if you want.

sage env:setup
```

4. Install and configure required environments
```bash
# It's automatic, you will prompt to enter your password once and two times MySQL root password.
# If you don't have installed MySQL before, just press enter (there is no password by default).
# After installation MySQL root password is 1 (until you changed it in ~/.sage/config.php config in node mysql.password)

sage env:install
```

5. [Optional step] Install Bash completion for the application
```bash
sage env:completion
```

6. Ready to use
```bash
# Displays a list of supported commands with short descriptions

sage list
```
