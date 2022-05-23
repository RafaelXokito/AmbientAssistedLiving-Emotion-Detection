package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.Date;

public class NotificationDTO {
    private long id;
    private String clientEmail;
    private String title;
    private String content;
    private Date created_at;
    private boolean notificationSeen;
    public NotificationDTO() {
    }

    public NotificationDTO(long id, String clientEmail, String title, String content, boolean notificationSeen) {
        this.id = id;
        this.clientEmail = clientEmail;
        this.title = title;
        this.content = content;
        this.notificationSeen = notificationSeen;
    }

    public NotificationDTO(long id, String clientEmail, String title, String content, boolean notificationSeen, Date created_at) {
        this.id = id;
        this.clientEmail = clientEmail;
        this.title = title;
        this.content = content;
        this.created_at = created_at;
        this.notificationSeen = notificationSeen;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getClientEmail() {
        return clientEmail;
    }

    public void setClientEmail(String clientEmail) {
        this.clientEmail = clientEmail;
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

    public Date getCreated_at() {
        return created_at;
    }

    public void setCreated_at(Date created_at) {
        this.created_at = created_at;
    }

    public boolean isNotificationSeen() {
        return notificationSeen;
    }

    public void setNotificationSeen(boolean notificationSeen) {
        this.notificationSeen = notificationSeen;
    }
}
