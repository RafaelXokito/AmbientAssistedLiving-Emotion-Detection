package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.Date;

public class LogDTO {
    private long id;
    private String clientEmail;
    private String process;
    private String macaddress;
    private String content;
    private Date created_at;
    
    public LogDTO() {
    }

    public LogDTO(long id, String clientEmail, String macaddress, String process, String content, Date created_at) {
        this.id = id;
        this.clientEmail = clientEmail;
        this.macaddress = macaddress;
        this.process = process;
        this.content = content;
        this.created_at = created_at;
    }

    public LogDTO(String clientEmail, String macaddress, String process, String content) {
        this.clientEmail = clientEmail;
        this.macaddress = macaddress;
        this.process = process;
        this.content = content;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getClientEmail() {
        return clientEmail;
    }

    public void setClientEmail(String clientEmail) {
        this.clientEmail = clientEmail;
    }

    public String getProcess() {
        return process;
    }

    public void setProcess(String process) {
        this.process = process;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public Date getCreated_at() {
        return created_at;
    }

    public void setCreated_at(Date created_at) {
        this.created_at = created_at;
    }

    public String getMacaddress() {
        return macaddress;
    }

    public void setMacaddress(String macaddress) {
        this.macaddress = macaddress;
    }

    
}

