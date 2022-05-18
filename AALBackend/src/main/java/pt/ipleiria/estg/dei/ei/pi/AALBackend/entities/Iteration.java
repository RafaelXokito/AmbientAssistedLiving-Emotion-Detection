package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;


import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.util.List;
import java.util.Date;

import java.io.Serializable;

@NamedQueries({
    @NamedQuery(
            name = "getAllIterations",
            query = "SELECT i FROM Iteration i"
    )
})

@Table(name = "ITERATIONS")
@Entity
public class Iteration implements Serializable{
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    @NotNull
    private String macAddress;
    @NotNull
    @ManyToOne
    private Emotion emotion; //Emotion AI
    @NotNull
    @ManyToOne
    private Client client;
    @OneToMany(mappedBy = "iteration", cascade = CascadeType.REMOVE)
    private List<Frame> frames;
    @Temporal(TemporalType.TIMESTAMP)
    private Date created_at;
    @Version
    private int version;

    public Iteration() {
        this.macAddress = "";
        this.client = new Client();
        this.emotion = new Emotion();
    }

    public Iteration(String macAddress, Emotion emotion, Client client){
        this.macAddress = macAddress;
        this.client = client;
        this.emotion = emotion;
    }

    public Long getId(){
        return id;
    }

    public Client getClient() {
        return client;
    }

    public void setClient(Client client) {
        this.client = client;
    }

    public Emotion getEmotion() {
        return emotion;
    }

    public void setEmotion(Emotion emotion) {
        this.emotion = emotion;
    }

    public String getMacAddress() {
        return macAddress;
    }

    public void setMacAddress(String macAddress) {
        this.macAddress = macAddress;
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

    @PrePersist
    protected void onCreate() {
        this.created_at = new Date();
    }

    public Date getCreated_at() {
        return created_at;
    }

}
