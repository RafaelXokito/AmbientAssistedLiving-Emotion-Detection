package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import java.io.Serializable;
import java.util.List;

import javax.persistence.*;

@NamedQueries({
        @NamedQuery(
                name = "getAllAdministrators",
                query = "SELECT admin FROM Administrator admin ORDER BY admin.name"
        )
})
@Table(name = "ADMINISTRATORS")
@Entity
public class Administrator extends Person implements Serializable {

    @OneToMany(mappedBy = "administrator", cascade = CascadeType.REMOVE)
    private List<Client> clients;

    public Administrator() {
        super();
    }
    public Administrator(String name, String email, String password) {
        super(name,email,password);
    }

    public void setClients(List<Client> clients){
        this.clients = clients;
    }

    public List<Client> getClients(){
        return clients;
    }

    public Client addClient(Client client) {
        if (client != null && !this.clients.contains(client)) {
            this.clients.add(client);
            return client;
        }
        return null;
    }
}
