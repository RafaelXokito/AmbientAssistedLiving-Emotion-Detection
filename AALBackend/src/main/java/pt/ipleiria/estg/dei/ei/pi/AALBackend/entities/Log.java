package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;


import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.util.List;
import java.util.Date;

import java.io.Serializable;

@NamedQueries({
    @NamedQuery(
            name = "getAllLogs",
            query = "SELECT l FROM Log l"
    )
})

@Table(name = "LOGS")
@Entity
public class Log implements Serializable{
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    @NotNull
    private String macAddress;
    @NotNull
    private String process;
    @NotNull
    private String content;
    @NotNull
    @ManyToOne
    private Client client;
    @Temporal(TemporalType.TIMESTAMP)
    private Date created_at;

    public Log() {
        this.macAddress = "";
        this.client = new Client();
        this.process = "";
        this.content = "";
    }

    public Log(String macAddress, String process,String content, Client client){
        this.macAddress = macAddress;
        this.client = client;
        this.process = process.toLowerCase();
        this.content = content;
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

    public String getProcess() {
        return process;
    }

    public void setProcess(String process) {
        this.process = process;
    }

    public String getMacAddress() {
        return macAddress;
    }

    public void setMacAddress(String macAddress) {
        this.macAddress = macAddress;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public void setCreated_at(Date created_at) {
        this.created_at = created_at;
    }

    @PrePersist
    protected void onCreate() {
        this.created_at = new Date();
    }

    public Date getCreated_at() {
        return created_at;
    }

}
