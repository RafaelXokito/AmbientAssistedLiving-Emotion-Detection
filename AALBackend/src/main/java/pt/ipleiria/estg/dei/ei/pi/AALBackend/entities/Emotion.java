package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

@NamedQueries({
        @NamedQuery(
                name = "getAllEmotions",
                query = "SELECT e FROM Emotion e"
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
        @Version
        private int version;

        public Emotion() {
                this.name = "";
                this.group = "";
                this.frames = new ArrayList<>();
        }

        public Emotion(String name, String group) {
                this.name = name.toLowerCase();
                this.group = group.toLowerCase();
                this.frames = new ArrayList<>();
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
}
