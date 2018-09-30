
## Sage

Sage is a development environment for macOS High Sierra.


## Installation

1. Since Sage depends on Brew. Install or update [Homebrew](https://brew.sh/) to the latest version using brew update.
```bash
# if not installed
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"

# add the next line into your ~/.bash_profile or ~/.bashrc file (create if not exists)

export PATH="$PATH:$HOME/bin:$HOME/.composer/vendor/bin"


# if installed

brew update
brew upgrade
```

2. Download phar package from [the latest release](https://github.com/ytorbyk/sage/releases/latest) and put it in `/usr/local/bin` folder. So it will be accessible in Terminal everywhere
```bash
curl -L https://github.com/ytorbyk/sage/releases/download/0.8.4/sage.phar > $HOME/bin/sage
chmod +x $HOME/bin/sage
```

3. [Optional step] Customize configuration
```bash
# It creates configuration dump ~/xSage/config.php.
# You can customize and move it to ~/.sage/config.php before next step if you want.

sage env:config-dump
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
