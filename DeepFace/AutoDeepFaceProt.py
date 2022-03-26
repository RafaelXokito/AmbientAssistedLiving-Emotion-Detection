from deepface import DeepFace
from tqdm import tqdm
import os
import cv2
import pandas as pd
import matplotlib.pyplot as plt

data_dir = "../input/created_dataset" #  "input/Train" 

limitFilesPerFolder = 1000 # 5

labels = next(os.walk(data_dir))[1]

df_all = pd.DataFrame()

accuracyByLabel = []

sum = 0
total = 0

for label in labels:

    path = os.path.join(data_dir, label)

    sumLabel = 0

    for imgName in tqdm(os.listdir(path)[:limitFilesPerFolder], desc=label):
        try:
            imgPath = os.path.join(path, imgName)
            img = cv2.imread(imgPath)
            obj = DeepFace.analyze(img, actions = ['emotion'])
            
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
                
        except Exception as e:
            print(e)
        
    accuracyByLabel.append(sumLabel/len(os.listdir(path)[:limitFilesPerFolder]))
    
df_all.to_csv('deepFace_painAnalysis.csv', encoding='utf-8')

print("\nOverall Accuracy: " +str((sum/total)*100)+" %")

plt.bar(labels, accuracyByLabel)
plt.suptitle('Precisão DeepFace para Pain Dataset (100 img)')
plt.show()
