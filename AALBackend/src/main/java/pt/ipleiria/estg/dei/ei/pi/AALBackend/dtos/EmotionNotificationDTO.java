package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class EmotionNotificationDTO {
    private long id;
    private String emotion_name;
    private String client_email;
    private double accuracyLimit;
    private long durationSeconds;

    public EmotionNotificationDTO() {
        this.id = -1;
        this.emotion_name = null;
        this.client_email = null;
        this.accuracyLimit = 0;
        this.durationSeconds = 0;
    }

    public EmotionNotificationDTO(String emotion_name, String client_email, double accuracyLimit, long durationSeconds) {
        this.id = -1;
        this.emotion_name = emotion_name;
        this.client_email = client_email;
        this.accuracyLimit = accuracyLimit;
        this.durationSeconds = durationSeconds;
    }

    public EmotionNotificationDTO(long id, String emotion_name, String client_email, double accuracyLimit, long durationSeconds) {
        this.id = id;
        this.emotion_name = emotion_name;
        this.client_email = client_email;
        this.accuracyLimit = accuracyLimit;
        this.durationSeconds = durationSeconds;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getEmotion_name() {
        return emotion_name;
    }

    public void setEmotion_name(String emotion_name) {
        this.emotion_name = emotion_name;
    }

    public String getClient_email() {
        return client_email;
    }

    public void setClient_email(String client_email) {
        this.client_email = client_email;
    }

    public double getAccuracyLimit() {
        return accuracyLimit;
    }

    public void setAccuracyLimit(double accuracyLimit) {
        this.accuracyLimit = accuracyLimit;
    }

    public long getDurationSeconds() {
        return durationSeconds;
    }

    public void setDurationSeconds(long durationSeconds) {
        this.durationSeconds = durationSeconds;
    }
}
