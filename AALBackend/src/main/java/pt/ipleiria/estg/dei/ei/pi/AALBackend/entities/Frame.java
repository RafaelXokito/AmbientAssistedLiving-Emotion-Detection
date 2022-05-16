package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import com.sun.istack.Nullable;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

@NamedQueries({
    @NamedQuery(
            name = "getAllFrames",
            query = "SELECT f FROM Frame f"
    )
})


@Table(name = "FRAMES")
@Entity
public class Frame implements Serializable{
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    @NotNull
    private String name;
    @NotNull
    private Double accuracy;
    @NotNull
    private String path;
    @ManyToOne
    private Iteration iteration;
    @ManyToOne
    @Nullable
    private Emotion emotion; //Human Labelling - Emotion classified
    @OneToMany(mappedBy = "frame", cascade = CascadeType.PERSIST)
    private List<Classification> predictions;
    @Temporal(TemporalType.TIMESTAMP)
    private Date updated_at;
    @NotNull
    @Temporal(TemporalType.TIMESTAMP)
    private Date createDate;
    @Version
    private int version;

    public Frame(){
        this.path = "";
        this.iteration = new Iteration();
        this.name = "";
        this.accuracy = null;
        this.createDate = new Date();
        this.predictions = new ArrayList<>();
    }

    public Frame(String name, Double accuracy, String path, Iteration iteration, Date createDate){
        this.path = path;
        this.iteration = iteration;
        this.accuracy = accuracy;
        this.name = name;
        this.createDate = createDate;
        this.predictions = new ArrayList<>();
    }

    public Date getCreateDate() {
        return createDate;
    }

    public void setCreateDate(Date createDate) {
        this.createDate = createDate;
    }

    public Long getId(){
        return id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }
    
    public String getPath() {
        return path;
    }

    public void setPath(String path) {
        this.path = path;
    }

    public Iteration getIteration() {
        return iteration;
    }

    public void setIteration(Iteration iteration) {
        this.iteration = iteration;
    }

    public Emotion getEmotion() {
        return emotion;
    }

    public void setEmotion(Emotion emotion) {
        this.emotion = emotion;
    }

    @PreUpdate
    protected void onUpdate() {
        this.updated_at = new Date();
    }

    public Double getAccuracy() {
        return accuracy;
    }

    public void setAccuracy(Double accuracy) {
        this.accuracy = accuracy;
    }

    public List<Classification> getPredictions() {
        return predictions;
    }

    public void setPredictions(List<Classification> predictions) {
        this.predictions = predictions;
    }

    public Classification addPrediction(Classification classification){
        if (classification != null && !this.predictions.contains(classification)) {
            this.predictions.add(classification);
            return classification;
        }
        return null;
    }
}
