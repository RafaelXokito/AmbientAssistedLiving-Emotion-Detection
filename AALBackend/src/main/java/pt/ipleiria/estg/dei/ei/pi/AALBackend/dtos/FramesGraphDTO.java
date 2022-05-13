package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.Date;

public class FramesGraphDTO {
    private long id;
    private String emotion_predicted;
    private String emotion_classified;
    private double accuracy;
    private Date createDate;
    
    public FramesGraphDTO(long id, String emotion_predicted, String emotion_classified, double accuracy,
            Date createDate) {
        this.id = id;
        this.emotion_predicted = emotion_predicted;
        this.emotion_classified = emotion_classified;
        this.accuracy = accuracy;
        this.createDate = createDate;
    }

    public FramesGraphDTO() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getEmotion_predicted() {
        return emotion_predicted;
    }

    public void setEmotion_predicted(String emotion_predicted) {
        this.emotion_predicted = emotion_predicted;
    }

    public String getEmotion_classified() {
        return emotion_classified;
    }

    public void setEmotion_classified(String emotion_classified) {
        this.emotion_classified = emotion_classified;
    }

    public double getAccuracy() {
        return accuracy;
    }

    public void setAccuracy(double accuracy) {
        this.accuracy = accuracy;
    }

    public Date getCreateDate() {
        return createDate;
    }

    public void setCreateDate(Date createDate) {
        this.createDate = createDate;
    }

    
}
