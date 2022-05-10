package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import com.sun.istack.Nullable;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;
import java.util.Date;

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
    private String path;
    @ManyToOne
    private Iteration iteration;
    @ManyToOne
    @Nullable
    private Emotion emotion; //Human Labelling - Emotion classified
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
        this.createDate = new Date();
    }

    public Frame(String name, String path, Iteration iteration, Date createDate){
        this.path = path;
        this.iteration = iteration;
        this.name = name;
        this.createDate = createDate;
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

}
