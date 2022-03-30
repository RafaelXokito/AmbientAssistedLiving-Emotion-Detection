from deepface import DeepFace
from tqdm import tqdm
import os
import cv2
import pandas as pd
import matplotlib.pyplot as plt

data_dir = "../input/Train"

face_cascade_name = cv2.data.haarcascades + 'haarcascade_frontalface_alt.xml'  #getting a haarcascade xml file
face_cascade = cv2.CascadeClassifier()  #processing it for our project
if not face_cascade.load(cv2.samples.findFile(face_cascade_name)):  #adding a fallback event
    print("Error loading xml file")


limitFilesPerFolder = 8
img_size = 224

labels = next(os.walk(data_dir))[1]

df_all = pd.DataFrame()

accuracyByLabel = []

models = ["VGG-Face", "Facenet", "Facenet512", "OpenFace", "DeepFace", "DeepID", "ArcFace", "Dlib"]
backends = ['opencv', 'ssd', 'dlib', 'mtcnn', 'retinaface', 'mediapipe']

sum = 0
total = 0

for label in labels:

    path = os.path.join(data_dir, label)

    sumLabel = 0
    
    verticalImages = []

    for imgName in tqdm(os.listdir(path)[:limitFilesPerFolder], desc=label):
        try:
            imgPath = os.path.join(path, imgName) # caminho da imagem
            img = cv2.imread(imgPath) # ler a imagem

            img = cv2.resize(img, (img_size, img_size)) # resize da imagem

            #gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)  # change the color
            #face = face_cascade.detectMultiScale(gray,scaleFactor=1.1,minNeighbors=5) # deteta os rostos
            #for x,y,w,h in face:
                #roi=cv2.rectangle(img,(x,y),(x+w,y+h),(0,0,255),1)  # RoI (Region of Interest)
                
                #obj = DeepFace.analyze(roi, actions = ['emotion']) # DeepFace analisa o RoI
            obj = DeepFace.analyze(img, actions = ['emotion']) # DeepFace analisa a imagem inicial

            cv2.rectangle(img,(obj["region"]["x"],obj["region"]["y"]),(obj["region"]["x"]+obj["region"]["w"],obj["region"]["y"]+obj["region"]["h"]),(0,0,255),1)  # RoI (Region of Interest)

            obj["emotionFolder"] = label
            obj["imageName"] = imgName

            if label == obj["dominant_emotion"]:
                sum = sum +1
                sumLabel = sumLabel +1
            
            total = total+1

            df = pd.json_normalize(obj)

            if df_all.empty:
                df_all = df
            else:
                df_all = df_all.append(df)

            cv2.putText(img,"Label: "+label,(25, 25), cv2.FONT_HERSHEY_COMPLEX, 0.5, (0,255,0), 1)
            cv2.putText(img,"Predicted: "+str(obj["dominant_emotion"]),(25, 50), cv2.FONT_HERSHEY_COMPLEX, 0.5, (0,255,0), 1)
            cv2.putText(img,"Confidence: "+str(round(pd.json_normalize(obj["emotion"]).max(axis=1)[0])),(25, 75), cv2.FONT_HERSHEY_COMPLEX, 0.5, (0,255,0), 1)
            
            if len(verticalImages) == 0:
                verticalImages = cv2.hconcat([img])
            else:
                verticalImages = cv2.hconcat([verticalImages,img])
            
        except Exception as e:
            print(e)
    
    cv2.imshow("Horizontal Images by "+label,verticalImages)
    cv2.waitKey(0)
    accuracyByLabel.append(sumLabel/len(os.listdir(path)[:limitFilesPerFolder]))
    
df_all.to_csv('DeepFaceAnalysis.csv', encoding='utf-8')

print("\nOverall Accuracy: " +str((sum/total)*100)+" %")

plt.bar(labels, accuracyByLabel)
plt.suptitle('Precisão Por Emoção DeepFace')
plt.show()
