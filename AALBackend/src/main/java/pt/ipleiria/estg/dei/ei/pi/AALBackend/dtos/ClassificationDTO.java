package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class ClassificationDTO {
    private long id;
    private EmotionDTO emotion;
    private double accuracy;

    public ClassificationDTO(long id, EmotionDTO emotion, double accuracy) {
        this.id = id;
        this.emotion = emotion;
        this.accuracy = accuracy;
    }

    public ClassificationDTO(EmotionDTO emotion, double accuracy) {
        this.emotion = emotion;
        this.accuracy = accuracy;
    }

    public ClassificationDTO() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public EmotionDTO getEmotion() {
        return emotion;
    }

    public void setEmotion(EmotionDTO emotion) {
        this.emotion = emotion;
    }

    public double getAccuracy() {
        return accuracy;
    }

    public void setAccuracy(double accuracy) {
        this.accuracy = accuracy;
    }
}
