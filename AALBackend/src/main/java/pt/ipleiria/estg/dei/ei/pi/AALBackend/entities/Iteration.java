package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;


import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.util.List;

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
    private Client client;
    @OneToMany(mappedBy = "iteration", cascade = CascadeType.REMOVE)
    private List<Frame> frames;

    public Iteration() {

    }

    public Iteration(String macAddress, Client client){
        this.macAddress = macAddress;
        this.client = client;
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

    public void addFrame(Frame frame){
        if (frames.contains(frame))
            return;

        this.frames.add(frame);
    }

}
