
## Sage

Sage is helper for development environment on macOS (High Sierra, Mojave, Catalina and Big Sur on intel).


## Installation

1. Since Sage depends on Brew. Install or update [Homebrew](https://brew.sh/) to the latest version using brew update.
```bash
# if not installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# add the next line into your ~/.bash_profile file (create if not exists)

export PATH="$PATH:/usr/local/sbin:$HOME/bin:$HOME/.composer/vendor/bin"


# if installed

brew update
brew upgrade
```
2. Since there is no php by default in macOS it hsould be istalled manually via brew. 
```bash
brew install shivammathur/php/php@7.3
```

3. Download phar package from [the latest release](https://github.com/ytorbyk/sage/releases/latest) and put it in `$HOME/bin` folder.
```bash
curl -L https://github.com/ytorbyk/sage/releases/latest/download/sage.phar > $HOME/bin/bin-sage
chmod +x $HOME/bin/bin-sage
```
4. Create $HOME/bin/sage txt file with the next contend
```bash
#!/usr/bin/env bash
/usr/local/opt/php@7.3/bin/php "$HOME/bin/bin-sage" "$@"
```
5. Make the txt file executable
```bash
chown +x $HOME/bin/sage
```

6. [Optional step] Customize configuration
```bash
# It creates configuration dump ~/xSage/config.php.
# You can customize and move it to ~/.sage/config.php before next step if you want.

sage env:config-dump
```

7. Install and configure required environments
```bash
# It's automatic, you will prompt to enter your password once and two times MySQL root password.
# If you don't have installed MySQL before, just press enter (there is no password by default).
# After installation MySQL root password is 1 (until you changed it in ~/.sage/config.php config in node mysql.password)

sage env:install
```

8. [Optional step] Install Bash completion for the application
```bash
sage env:completion
```

9. Ready to use
```bash
# Displays a list of supported commands with short descriptions

sage list
```
