@ECHO OFF
ECHO Ambient Assisted Living - Startup File
python3 AAL.py -t 30 > AALLog.txt &
python3 Websocket.py > WebsocketLog.txt &
python3 Retrain.py > RetrainLog.txt &
ECHO EOF
PAUSE