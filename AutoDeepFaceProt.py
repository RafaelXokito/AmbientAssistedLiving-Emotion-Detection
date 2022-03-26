from deepface import DeepFace
from tqdm import tqdm
import os
import cv2

data_dir = "input/Train"

limitFilesPerFolder = 5

labels = next(os.walk(data_dir))[1]

for label in labels:

    path = os.path.join(data_dir, label)

    for img in tqdm(os.listdir(path)[:limitFilesPerFolder]):
        try:
            img = cv2.imread(os.path.join(path, img))
            obj = DeepFace.analyze(img, actions = ['emotion'])
            print(obj)
            exit()
        except Exception as e:
            print(e)