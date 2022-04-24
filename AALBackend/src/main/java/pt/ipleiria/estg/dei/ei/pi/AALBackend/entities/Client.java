package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

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
    private int age;
    @NotNull
    private String contact;
    @OneToMany(mappedBy = "client", cascade = CascadeType.REMOVE)
    private List<Iteration> iterations;
    @ManyToOne
    private Administrator administrator;

    @Version
    private int version;

    public Client() {
        super();
        this.age = -1;
        this.contact = "";
        this.administrator = new Administrator();
    }

    public Client(String email, String password, String name, int age, String contact, Administrator administrator) {
        super(name,email,password);
        this.age = age;
        this.contact = contact;
        this.administrator = administrator;
    }

    public int getAge() {
        return age;
    }

    public void setAge(int age) {
        this.age = age;
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

    public void setAdministrator(Administrator administrator){
        this.administrator = administrator;
    }
    
    public Administrator getAdministrator(){
        return administrator;
    }
}
