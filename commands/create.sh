MACHINE_NAME=$1
LABEL=$2
ORGANIZATION=$3

# Script location
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Create log file
LOG="$DIR/../logs/$MACHINE_NAME.create.log"
touch $LOG

terminus site:create $MACHINE_NAME "$LABEL" "Drupal 8" --org="$ORGANIZATION" > $LOG 2>&1 &
PID=$!

# Fired on finish.
function FINISH {
  rm $LOG
  kill $PID 2> /dev/null
}

# If this script is killed, kill the `cp'.
trap FINISH 2> /dev/null EXIT;

# The initial log message
NOTICE="[notice] Initializing site creation..."
# The initial background log message.
NOTICE_BG=""

# Watch for the process to finish.
while kill -0 $PID 2> /dev/null;
do
    NOTICE_BG_CURRENT=$(tail -1 $LOG)
    if [[ "$NOTICE_BG_CURRENT" != "$NOTICE_BG" ]]
    then
      NOTICE_BG=$NOTICE_BG_CURRENT
      NOTICE="$NOTICE_BG"
    fi
    NOTICE="$NOTICE."
    echo "$NOTICE" | xargs
    sleep 4
done

# Check if last logged message is an error.
LAST_LOG=$(tail -1 $LOG)
if [[ $LAST_LOG == *\[error\]* ]]
then
  echo "$LAST_LOG" | xargs
else
  echo "[success] Site creation complete!"
fi

# Disable the trap on a normal exit.
trap - EXIT

FINISH
