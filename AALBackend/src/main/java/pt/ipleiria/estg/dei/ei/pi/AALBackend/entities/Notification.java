package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.util.Date;

@NamedQueries({
        @NamedQuery(
                name = "getAllNotifications",
                query = "SELECT n FROM Notification n ORDER BY n.created_at DESC"
        ),
        @NamedQuery(
                name = "getAllNotificationsByClient",
                query = "SELECT n FROM Notification n WHERE n.client.id = :id ORDER BY n.created_at DESC"
        ),
        @NamedQuery(
                name = "getEmotionWithTheMostNotificationsByClient",
                query = "SELECT n.emotion.name, count(n.emotion) FROM Notification n WHERE n.client.id = :id GROUP BY n.emotion ORDER BY 1 DESC"
        ),
        @NamedQuery(
                name = "getEmotionWithTheMostNotifications",
                query = "SELECT n.emotion.name, count(n.emotion) FROM Notification n GROUP BY n.emotion ORDER BY 1 DESC"
        ),
        @NamedQuery(
                name = "getEmotionWithTheLeastNotificationsConfiguredByClient",
                query = "SELECT n.emotion.name, count(n.emotion) FROM EmotionNotification em JOIN Notification n ON n.emotion.name = em.emotion.name WHERE em.client.id = :id GROUP BY n.emotion ORDER BY 1 ASC"
        ),
        @NamedQuery(
                name = "getEmotionWithTheLeastNotificationsConfigured",
                query = "SELECT n.emotion.name, count(n.emotion) FROM EmotionNotification em JOIN Notification n ON n.emotion.name = em.emotion.name GROUP BY n.emotion ORDER BY 1 ASC"
        ),
})

@Table(name = "NOTIFICATIONS")
@Entity
public class Notification {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    @NotNull
    private String title;
    @NotNull
    @Column(columnDefinition="TEXT", length = 300)
    private String content;
    @NotNull
    @ManyToOne
    private Client client;
    @Temporal(TemporalType.TIMESTAMP)
    private Date created_at;
    @NotNull
    private Boolean notificationSeen;
    @NotNull
    @ManyToOne
    private Emotion emotion;
    @NotNull
    private Double accuracy;
    @NotNull
    private Double duration;

    @Version
    private int version;

    public Notification() {
        this.client = new Client();
        this.title = "";
        this.content = "";
        this.notificationSeen = null;
        this.emotion = new Emotion();
        this.accuracy = null;
        this.duration = null;
    }

    public Notification(String title, String content, Client client, Emotion emotion, Double accuracy, Double duration) {
        this.title = title;
        this.content = content;
        this.client = client;
        this.notificationSeen = false;
        this.emotion = emotion;
        this.accuracy = accuracy;
        this.duration = duration;
    }

    public Boolean getNotificationSeen() {
        return notificationSeen;
    }

    public void setNotificationSeen(Boolean notificationSeen) {
        this.notificationSeen = notificationSeen;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public Client getClient() {
        return client;
    }

    public void setClient(Client client) {
        this.client = client;
    }

    public Date getCreated_at() {
        return created_at;
    }

    public void setCreated_at(Date created_at) {
        this.created_at = created_at;
    }
    @PrePersist
    protected void onCreate() {
        this.created_at = new Date();
    }

    public Emotion getEmotion() {
        return emotion;
    }

    public void setEmotion(Emotion emotion) {
        this.emotion = emotion;
    }

    public Double getAccuracy() {
        return accuracy;
    }

    public void setAccuracy(Double accuracy) {
        this.accuracy = accuracy;
    }

    public Double getDuration() {
        return duration;
    }

    public void setDuration(Double duration) {
        this.duration = duration;
    }
}
