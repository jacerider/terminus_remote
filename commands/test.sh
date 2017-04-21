MACHINE_NAME=$1

# Script location
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Create log file
LOG="$DIR/../logs/$MACHINE_NAME.create.bg.log"

echo "Log location: $LOG"
touch $LOG

# Create if necessary
if [ ! -f $LOG ]; then
  echo "<br>Log file does not exist!"
fi

terminus aliases

rm $LOG
exit 1

touch $LOG

# terminus drush "$1.dev"  -- site-install --site-name="My Sweetness" -y -v

terminus aliases 2> $LOG &
# terminus site:create $MACHINE_NAME "Big Cyle" "Drupal 8" --org="August Ash" > $LOG 2>&1 &
# terminus drush "$MACHINE_NAME.dev"  -- site-install --site-name="My Sweetness" -y -v > $LOG 2>&1 &
PID=$!

# Fired on finish.
function FINISH {
  rm $LOG
  kill $PID 2> /dev/null
}

# If this script is killed, kill the `cp'.
trap FINISH 2> /dev/null EXIT;

# The initial log message
NOTICE="[notice] Running tests..."
# The initial background log message.
NOTICE_BG=""

# Watch for the process to finish.
while kill -0 $PID 2> /dev/null;
do
    NOTICE_BG_CURRENT=$(tail -1 $LOG)
    if [[ "$NOTICE_BG_CURRENT" != "$NOTICE_BG" ]]
    then
      NOTICE_BG=$NOTICE_BG_CURRENT
      NOTICE="$NOTICE_BG..."
    fi
    NOTICE="$NOTICE."
    echo $NOTICE
    sleep 4
done

# Echo last logged message.
echo $(tail -1 $LOG)

# Disable the trap on a normal exit.
trap - EXIT

FINISH
