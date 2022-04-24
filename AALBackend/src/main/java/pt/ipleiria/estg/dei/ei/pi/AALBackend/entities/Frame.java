package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;

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
    private Long id;

    @NotNull
    private String path;
    @ManyToOne
    private Iteration iteration;

    public Frame(){
        
    }

    public Frame(String path, Iteration iteration){
        this.path = path;
        this.iteration = iteration;
    }

    public Long getId(){
        return id;
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

}
