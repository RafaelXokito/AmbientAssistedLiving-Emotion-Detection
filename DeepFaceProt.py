import cv2
from deepface import DeepFace
import numpy as np

face_cascade_name = cv2.data.haarcascades + 'haarcascade_frontalface_alt.xml'  #getting a haarcascade xml file
face_cascade = cv2.CascadeClassifier()  #processing it for our project
if not face_cascade.load(cv2.samples.findFile(face_cascade_name)):  #adding a fallback event
    print("Error loading xml file")

video=cv2.VideoCapture(0)  #requisting the input from the webcam or camera

while True:  #checking if are getting video feed and using it
    _,frame = video.read()
    
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)  #changing the video to grayscale to make the face analisis work properly
    face = face_cascade.detectMultiScale(gray,scaleFactor=1.1,minNeighbors=5)

    for x,y,w,h in face:
      img=cv2.rectangle(frame,(x,y),(x+w,y+h),(0,0,255),1)  #making a recentangle to show up and detect the face and setting it position and colour
   
      #making a try and except condition in case of any errors
      try:
          analyze = DeepFace.analyze(img,actions=['emotion'])  #same thing is happing here as the previous example, we are using the analyze class from deepface and using ‘frame’ as input
          print(analyze['dominant_emotion'])  #here we will only go print out the dominant emotion also explained in the previous example
      except:
          print("no face")

    #this is the part where we display the output to the user
    cv2.imshow('video', frame)
    key=cv2.waitKey(1) 
    if key==ord('q'):   # here we are specifying the key which will stop the loop and stop all the processes going
        break
video.release()