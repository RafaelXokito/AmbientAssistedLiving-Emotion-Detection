package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import java.util.Date;
import java.util.List;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;

@NamedQueries({
        @NamedQuery(
                name = "getAllClients",
                query = "SELECT c FROM Client c ORDER BY c.name"
        )
})

@Table(name = "CLIENTS")
@Entity
public class Client extends Person implements Serializable {

    @NotNull
    @Temporal(TemporalType.TIMESTAMP)
    private Date birthDate;

    @NotNull
    private String contact;
    @OneToMany(mappedBy = "client", cascade = CascadeType.REMOVE)
    private List<Iteration> iterations;
    @OneToMany(mappedBy = "client", cascade = CascadeType.REMOVE)
    private List<Log> logs;
    @OneToMany(mappedBy = "client", cascade = CascadeType.PERSIST)
    private List<EmotionNotification> emotionNotifications;
    @OneToMany(mappedBy = "client", cascade = CascadeType.PERSIST)
    private List<Notification> notifications;
    @ManyToOne
    private Administrator administrator;

    @Version
    private int version;

    public Client() {
        super();
        this.birthDate = new Date();
        this.contact = "";
        this.administrator = new Administrator();
    }

    public Client(String email, String password, String name, Date birthDate, String contact, Administrator administrator) {
        super(name,email,password);
        this.birthDate = birthDate;
        this.contact = contact;
        this.administrator = administrator;
    }

    public Date getBirthDate() {
        return birthDate;
    }

    public void setBirthDate(Date birthDate) {
        this.birthDate = birthDate;
    }

    public String getContact() {
        return contact;
    }

    public void setContact(String contact) {
        this.contact = contact;
    }

    public List<Iteration> getIterations() {
        return iterations;
    }

    public void setIterations(List<Iteration> iterations) {
        this.iterations = iterations;
    }

    public void addIteration(Iteration iteration){
        if (iterations.contains(iteration))
            return;

        this.iterations.add(iteration);
    }

    public void addLog(Log log){
        if (logs.contains(log))
            return;

        this.logs.add(log);
    }

    public void setAdministrator(Administrator administrator){
        this.administrator = administrator;
    }
    
    public Administrator getAdministrator(){
        return administrator;
    }

    public List<Log> getLogs() {
        return logs;
    }

    public void setLogs(List<Log> logs) {
        this.logs = logs;
    }

    public List<EmotionNotification> getEmotionNotifications() {
        return emotionNotifications;
    }

    public void setEmotionNotifications(List<EmotionNotification> emotionsNotification) {
        this.emotionNotifications = emotionsNotification;
    }

    public EmotionNotification addEmotionNotification(EmotionNotification emotionNotification){
        if (emotionNotification != null && !this.emotionNotifications.contains(emotionNotification)) {
            this.emotionNotifications.add(emotionNotification);
            return emotionNotification;
        }
        return null;
    }

    public EmotionNotification removeEmotionNotification(EmotionNotification emotionNotification) {
        return emotionNotification != null && this.emotionNotifications.remove(emotionNotification) ? emotionNotification : null;
    }

    public Notification addNotification(Notification notification){
        if (notification != null && !this.notifications.contains(notification)) {
            this.notifications.add(notification);
            return notification;
        }
        return null;
    }



    public List<Notification> getNotifications() {
        return notifications;
    }

    public void setNotifications(List<Notification> notifications) {
        this.notifications = notifications;
    }
}
