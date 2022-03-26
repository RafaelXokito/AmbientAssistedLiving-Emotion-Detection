import json

# Convert Description to Percentage
def convertPercentage(description):
    if description == "VERY_UNLIKELY":
        return 0.2
    if description == "UNLIKELY":
        return 0.4
    if description == "POSSIBLE":
        return 0.6       
    if description == "LIKELY":
        return 0.8        
    return 1.0    

#open file
painAnalysisFile = open('cloudVision_painAnalysis.json')
 
# convert json to dictionary
dataPainAnalysis = json.load(painAnalysisFile)
 
for data in dataPainAnalysis['VisionAPIGoogle']:
    # retrieve important fields
    emotionsAnalysis = {}

    emotionsAnalysis["angerLikelihood"] = convertPercentage(data["faceAnnotations"][0]["angerLikelihood"])
    emotionsAnalysis["joyLikelihood"] = convertPercentage(data["faceAnnotations"][0]["joyLikelihood"])
    emotionsAnalysis["sorrowLikelihood"] = convertPercentage(data["faceAnnotations"][0]["sorrowLikelihood"])
    emotionsAnalysis["surpriseLikelihood"] = convertPercentage(data["faceAnnotations"][0]["surpriseLikelihood"])

    emotionsAnalysis["detectionConfidence"] = data["faceAnnotations"][0]["detectionConfidence"]
    emotionsAnalysis["landmarkingConfidence"] = data["faceAnnotations"][0]["landmarkingConfidence"]

    #replace data
    data["faceAnnotations"][0] = emotionsAnalysis


# Closing file
painAnalysisFile.close()

# create the new file
with open("cloudVision_painAnalysis_Processed.json", "w") as outfile:
    json.dump(dataPainAnalysis, outfile, indent=4)

