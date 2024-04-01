# https://patorjk.com/software/taag/#p=display&h=1&f=Slant&t=MyCademy%20%20KMS%20v2
cat <<'MSG'
__  __ _  _  ___      ____   ___           __   __    ___      _____
\ \/ /(_)(_)|__ \    / __ \ /   |  __  __ / /_ / /_  |__ \    / ___/ ___   _____ _   __ ___   _____
 \  // // / __/ /   / / / // /| | / / / // __// __ \ __/ /    \__ \ / _ \ / ___/| | / // _ \ / ___/
 / // // / / __/   / /_/ // ___ |/ /_/ // /_ / / / // __/    ___/ //  __// /    | |/ //  __// /
/_//_//_/ /____/   \____//_/  |_|\__,_/ \__//_/ /_//____/   /____/ \___//_/     |___/ \___//_/

MSG

echo "PHP version: ${PHP_VERSION}"
echo "DB driver: ${YII_DB_DRIVER}"

if ! shopt -oq posix; then
  if [ -f /usr/share/bash-completion/bash_completion ]; then
    . /usr/share/bash-completion/bash_completion
  elif [ -f /etc/bash_completion.d/yii ]; then
    . /etc/bash_completion.d/yii
  fi
fi
