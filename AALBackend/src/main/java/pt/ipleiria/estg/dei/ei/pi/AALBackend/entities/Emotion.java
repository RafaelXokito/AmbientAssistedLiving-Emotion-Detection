package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

@NamedQueries({
        @NamedQuery(
                name = "getAllEmotions",
                query = "SELECT e FROM Emotion e order by e.group"
        )
})

@Entity
@Table(name="EMOTIONS")
public class Emotion implements Serializable {
        @Id
        private String name;
        @Column(name="category")
        @NotNull
        private String group; // Positive, Negative, Neutral
        @OneToMany(mappedBy = "emotion", cascade = CascadeType.PERSIST)
        private List<Frame> frames;
        @OneToMany(mappedBy = "emotion", cascade = CascadeType.PERSIST)
        private List<Iteration> iterations;
        @OneToMany(mappedBy = "emotion", cascade = CascadeType.PERSIST)
        private List<Classification> classifications;
        @OneToMany(mappedBy = "emotion", cascade = CascadeType.PERSIST)
        private List<EmotionNotification> emotionNotifications;
        @OneToMany(mappedBy = "emotion", cascade = CascadeType.PERSIST)
        private List<Notification> notifications;

        @Version
        private int version;

        public Emotion() {
                this.name = "";
                this.group = "";
                this.frames = new ArrayList<>();
                this.iterations = new ArrayList<>();
                this.classifications = new ArrayList<>();
                this.emotionNotifications = new ArrayList<>();
        }

        public Emotion(String name, String group) {
                this.name = name.toLowerCase();
                this.group = group.toLowerCase();
                this.frames = new ArrayList<>();
                this.iterations = new ArrayList<>();
                this.classifications = new ArrayList<>();
                this.emotionNotifications = new ArrayList<>();
        }

        public String getName() {
                return name;
        }

        public void setName(String name) {
                this.name = name;
        }

        public String getGroup() {
                return group;
        }

        public void setGroup(String group) {
                this.group = group;
        }


        public List<Frame> getFrames() {
                return frames;
        }

        public void setFrames(List<Frame> frames) {
                this.frames = frames;
        }

        public Frame addFrame(Frame frame){
                if (frame != null && !this.frames.contains(frame)) {
                        this.frames.add(frame);
                        return frame;
                }
                return null;
        }

        public Iteration addIteration(Iteration iteration){
                if (iteration != null && !this.iterations.contains(iteration)) {
                        this.iterations.add(iteration);
                        return iteration;
                }
                return null;
        }

        public Classification addClassification(Classification classification){
                if (classification != null && !this.classifications.contains(classification)) {
                        this.classifications.add(classification);
                        return classification;
                }
                return null;
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

        public Notification addNotification(Notification notification){
                if (notification != null && !this.notifications.contains(notification)) {
                        this.notifications.add(notification);
                        return notification;
                }
                return null;
        }

        public List<Classification> getClassifications() {
                return classifications;
        }

        public void setClassifications(List<Classification> classifications) {
                this.classifications = classifications;
        }

        public List<Notification> getNotifications() {
                return notifications;
        }

        public void setNotifications(List<Notification> notifications) {
                this.notifications = notifications;
        }
}
