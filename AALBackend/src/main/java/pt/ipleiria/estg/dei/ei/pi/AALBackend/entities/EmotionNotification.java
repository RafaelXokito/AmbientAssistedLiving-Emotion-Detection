package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;
import java.time.Duration;

@NamedQueries({
        @NamedQuery(
                name = "getAllEmotionsNotifications",
                query = "SELECT e FROM EmotionNotification e"
        )
})

@Table(name = "EMOTIONSNOTIFICATIONS")
@Entity
public class EmotionNotification implements Serializable {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    @NotNull
    @ManyToOne
    private Emotion emotion;
    @NotNull
    @ManyToOne
    private Client client;
    @NotNull
    private Double accuracyLimit;
    @NotNull
    private Duration duration;
    @Version
    private int version;
    public EmotionNotification() {
    }

    public EmotionNotification(Emotion emotion, Client client, Double accuracyLimit, Duration duration) {
        this.emotion = emotion;
        this.client = client;
        this.accuracyLimit = accuracyLimit;
        this.duration = duration;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public Emotion getEmotion() {
        return emotion;
    }

    public void setEmotion(Emotion emotion) {
        this.emotion = emotion;
    }

    public Client getClient() {
        return client;
    }

    public void setClient(Client client) {
        this.client = client;
    }

    public Double getAccuracyLimit() {
        return accuracyLimit;
    }

    public void setAccuracyLimit(Double accuracyLimit) {
        this.accuracyLimit = accuracyLimit;
    }

    public Duration getDuration() {
        return duration;
    }

    public void setDuration(Duration duration) {
        this.duration = duration;
    }
}
