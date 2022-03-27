import json
import pandas as pd

# Extra functions
def diffs_deepface_cloudVision(deepFacePercent, cloudVisionPercent):
    range = []
    if cloudVisionPercent == 20:
        range = [0,20]
    elif cloudVisionPercent == 40:
        range = [20,40] 
    elif cloudVisionPercent == 60:
        range = [40,60]
    elif cloudVisionPercent == 80:
        range = [60,80]
    elif cloudVisionPercent == 100:
        range = [80,100]   

    deepFacePercent = round(deepFacePercent)

    minDiff = abs(deepFacePercent - range[0])
    maxDiff = abs(range[1] - deepFacePercent)
    avg = round((minDiff + maxDiff)/2)

    # return the average distance and the number of quadrants the value is from the range 
    if avg >= 0 and avg <= 10:
        return [avg, 0]  
    elif avg >= 11 and avg <= 30:
        return [avg, 1]   
    elif avg >= 31 and avg <= 50:
        return [avg, 2] 
    elif avg >= 51 and avg <= 70:
        return [avg, 3]  
    elif avg >= 71 and avg <= 90:
        return [avg, 4]     

def createEmotionData(feature_vector, deepface, cloudVision, avg, quadrant):
    feature_vector.append(round(deepface, 2))
    feature_vector.append(cloudVision)
    feature_vector.append(avg)
    feature_vector.append(quadrant)
    # quadrant is 0 or 1 (meaning the distance of the DF and CV were close)
    # if the prob is between 60 and 100
    if quadrant <= 1 and cloudVision >= 60:
        feature_vector.append(1) # 1 - True
    else:
        feature_vector.append(0) # 0 - False
    return feature_vector

# File Paths
cloudVisionPath = 'CloudVisionAPI/cloudVision_painAnalysis_Processed.json'
deepFacePath = 'DeepFace/deepFace_painAnalysis.csv'

# open cloudVision file
painAnalysis_CloudVisionFile = open(cloudVisionPath)
 
# convert json to dictionary
dataPainAnalysis_CloudVision = json.load(painAnalysis_CloudVisionFile)

# open DeepFace file
painAnalysis_DeepFace = pd.read_csv(deepFacePath)
feature_names = ["imageName", "emotion.angry", "emotion.happy", "emotion.surprise", "emotion.sad", "dominant_emotion"]
dataPainAnalysis_DeepFace = painAnalysis_DeepFace[feature_names]

header = ["anger_DeepFace", "anger_CloudVision", "anger_avg_distance", "anger_quadrants_distance", "anger_dominant", "happy_DeepFace", "happy_CloudVision", "happy_avg_distance", "happy_quadrants_distance", "happy_dominant", "surprise_DeepFace", "surprise_CloudVision", "surprise_avg_distance", "surprise_quadrants_distance", "surprise_dominant", "sad_DeepFace", "sad_CloudVision", "sad_avg_distance", "sad_quadrants_distance", "sad_dominant"]
data = []

for row, deepFaceImgData in enumerate(dataPainAnalysis_DeepFace.values):
    for cloudVisionData in dataPainAnalysis_CloudVision['VisionAPIGoogle']:
        if cloudVisionData["Image name"] == deepFaceImgData[0]: # images match
            angerCV = cloudVisionData["faceAnnotations"][0]["angerLikelihood"]
            happyCV = cloudVisionData["faceAnnotations"][0]["joyLikelihood"]
            surpriseCV = cloudVisionData["faceAnnotations"][0]["surpriseLikelihood"]
            sadCV = cloudVisionData["faceAnnotations"][0]["sorrowLikelihood"]
            angerDF = deepFaceImgData[1]
            happyDF = deepFaceImgData[2]
            surpriseDF = deepFaceImgData[3]
            sadDF = deepFaceImgData[4]

            # Calculate the diff of the percentages
            angerResults = diffs_deepface_cloudVision(angerDF, angerCV)
            happyResults = diffs_deepface_cloudVision(happyDF, happyCV)
            surpriseResults = diffs_deepface_cloudVision(surpriseDF, surpriseCV)
            sadResults = diffs_deepface_cloudVision(sadDF, sadCV)

            feature_vector = []
            feature_vector = createEmotionData(feature_vector, angerDF, angerCV, angerResults[0], angerResults[1])    
            feature_vector = createEmotionData(feature_vector, happyDF, happyCV, happyResults[0], happyResults[1])
            feature_vector = createEmotionData(feature_vector, surpriseDF, surpriseCV, surpriseResults[0], surpriseResults[1])
            feature_vector = createEmotionData(feature_vector, sadDF, sadCV, sadResults[0], sadResults[1])
            data.append(feature_vector)
            break


#Criar o csv
df = pd.DataFrame(data)
df.columns = header
df.to_csv('comparison_DeepFace_CloudVision.csv', index = False, header  = header)
painAnalysis_CloudVisionFile.close() 


