package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import io.smallrye.common.constraint.NotNull;

import javax.persistence.*;
import java.io.Serializable;

@NamedQueries({
        @NamedQuery(
                name = "getAllClassifications",
                query = "SELECT c FROM Classification c"
        )
})


@Table(name = "CLASSIFICATIONS")
@Entity
public class Classification implements Serializable {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne
    private Emotion emotion;

    @NotNull
    private Double accuracy;

    @ManyToOne
    private Frame frame;

    public Classification() {
    }

    public Classification(Emotion emotion, Double accuracy, Frame frame) {
        this.emotion = emotion;
        this.accuracy = accuracy;
        this.frame = frame;
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

    public Double getAccuracy() {
        return accuracy;
    }

    public void setAccuracy(Double accuracy) {
        this.accuracy = accuracy;
    }

    public Frame getFrame() {
        return frame;
    }

    public void setFrame(Frame frame) {
        this.frame = frame;
    }
}
