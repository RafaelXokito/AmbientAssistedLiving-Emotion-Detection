import json
import pandas as pd
import matplotlib.pyplot as plt

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

def dominantEmotionDF_CV(deepFaceDominant, feature_vector):
    emotions = [feature_vector[1], feature_vector[6], feature_vector[11], feature_vector[16]]
    names = ["angry", "happy", "surprise", "sad"]
    dominant = emotions.index(max(emotions))
    feature_vector.append(deepFaceDominant)
    feature_vector.append(names[dominant])
    return feature_vector


def func(pct):
  return "{:1.1f}%".format(pct)
 

# File Paths
cloudVisionPath = 'CloudVisionAPI/cloudVision_painAnalysis_Processed.json'
deepFacePath = 'DeepFace/deepFace_painAnalysis.csv'

# open cloudVision file
painAnalysis_CloudVisionFile = open(cloudVisionPath)
 
# convert json to dictionary
dataPainAnalysis_CloudVision = json.load(painAnalysis_CloudVisionFile)

# open DeepFace file
painAnalysis_DeepFace = pd.read_csv(deepFacePath)
feature_names = ["dominant_emotion","imageName", "emotion.angry", "emotion.happy", "emotion.surprise", "emotion.sad", "dominant_emotion"]
dataPainAnalysis_DeepFace = painAnalysis_DeepFace[feature_names]

header = ["anger_DeepFace", "anger_CloudVision", "anger_avg_distance", "anger_quadrants_distance", "anger_relevant", "happy_DeepFace", "happy_CloudVision", "happy_avg_distance", "happy_quadrants_distance", "happy_relevant", "surprise_DeepFace", "surprise_CloudVision", "surprise_avg_distance", "surprise_quadrants_distance", "surprise_relevant", "sad_DeepFace", "sad_CloudVision", "sad_avg_distance", "sad_quadrants_distance", "sad_relevant", "deepFace_dominant", "cloudVision_dominant"]
data = []
graphAngerDF = []
graphHappyDF = []
graphSurpriseDF = []
graphSadDF = []
graphAngerCV = []
graphHappyCV = []
graphSurpriseCV = []
graphSadCV = []
graphEmotionsCV = [0,0,0,0]
graphEmotionsDF = [0,0,0,0]
i = 0
for row, deepFaceImgData in enumerate(dataPainAnalysis_DeepFace.values):
    for cloudVisionData in dataPainAnalysis_CloudVision['VisionAPIGoogle']:
        if cloudVisionData["Image name"] == deepFaceImgData[1]: # images match
            angerCV = cloudVisionData["faceAnnotations"][0]["angerLikelihood"]
            happyCV = cloudVisionData["faceAnnotations"][0]["joyLikelihood"]
            surpriseCV = cloudVisionData["faceAnnotations"][0]["surpriseLikelihood"]
            sadCV = cloudVisionData["faceAnnotations"][0]["sorrowLikelihood"]
            angerDF = deepFaceImgData[2]
            happyDF = deepFaceImgData[3]
            surpriseDF = deepFaceImgData[4]
            sadDF = deepFaceImgData[5]

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
            
            feature_vector = dominantEmotionDF_CV(deepFaceImgData[0], feature_vector)
            data.append(feature_vector)

            # Add the emotion's percentage to each emotion
            graphAngerDF.append(angerDF)
            graphHappyDF.append(happyDF)
            graphSurpriseDF.append(surpriseDF)
            graphSadDF.append(sadDF)    
            graphAngerCV.append(angerCV)
            graphHappyCV.append(happyCV)
            graphSurpriseCV.append(surpriseCV)
            graphSadCV.append(sadCV)       

            # sum the emotions percentage
            graphEmotionsDF[0] = graphEmotionsDF[0] + angerDF
            graphEmotionsDF[1] = graphEmotionsDF[1] + happyDF
            graphEmotionsDF[2] = graphEmotionsDF[2] + surpriseDF
            graphEmotionsDF[3] = graphEmotionsDF[3] + sadDF

            graphEmotionsCV[0] = graphEmotionsCV[0] + angerCV
            graphEmotionsCV[1] = graphEmotionsCV[1] + happyCV
            graphEmotionsCV[2] = graphEmotionsCV[2] + surpriseCV
            graphEmotionsCV[3] = graphEmotionsCV[3] + sadCV

            i=i+1
            break

#Create the boxplot
fig = plt.figure(figsize =(40, 20))
graphDataBoxPlot = [graphAngerDF, graphAngerCV, graphHappyDF, graphHappyCV, graphSurpriseDF, graphSurpriseCV, graphSadDF, graphSadCV]

ax = fig.add_subplot(111)
ax.set_yticklabels(['Angry - DF', 'Angry - CV','Happy - DF', 'Happy - CV','Surprise - DF','Surprise - CV', 'Sad - DF', 'Sad - CV'])
plt.ylabel('Emotion')
plt.xlabel('Percentage')
plt.boxplot(graphDataBoxPlot, vert=False)

# calculate averages for pie charts
for x, emo in enumerate(graphEmotionsDF):
    graphEmotionsDF[x] = graphEmotionsDF[x]/i    
    graphEmotionsCV[x] = graphEmotionsCV[x]/i    


#create pie charts
labels = ["angry","happy","surprise","sad"]
figPie = plt.figure(figsize=(10,10),dpi=144)
ax1 = figPie.add_subplot(121)
ax1.pie(graphEmotionsCV, labels = labels, autopct=lambda pct: func(pct)) 
ax1.title.set_text('Cloud Vision')

ax2 = figPie.add_subplot(122)
ax2.pie(graphEmotionsDF, labels = labels, autopct=lambda pct: func(pct))    
ax2.title.set_text('Deep Face')
plt.show()

#Criar o csv
df = pd.DataFrame(data)
df.columns = header
df.to_csv('comparison_DeepFace_CloudVision.csv', index = False, header  = header)
painAnalysis_CloudVisionFile.close() 


