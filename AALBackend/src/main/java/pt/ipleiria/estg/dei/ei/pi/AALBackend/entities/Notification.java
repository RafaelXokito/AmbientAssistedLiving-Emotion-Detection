package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.util.Date;

@NamedQueries({
        @NamedQuery(
                name = "getAllNotifications",
                query = "SELECT n FROM Notification n ORDER BY n.created_at DESC"
        )
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
    @Version
    private int version;

    public Notification() {
        this.client = new Client();
        this.title = "";
        this.content = "";
    }

    public Notification(String title, String content, Client client) {
        this.title = title;
        this.content = content;
        this.client = client;
        this.notificationSeen = false;
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
}
