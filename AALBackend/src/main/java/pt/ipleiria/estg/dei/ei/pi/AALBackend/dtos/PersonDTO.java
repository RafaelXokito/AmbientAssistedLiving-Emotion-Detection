package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.Date;
import java.util.List;

public class PersonDTO {
    private long id;
    private String email;
    private String password;
    private String name;
    private String scope; //Administrator, Client


    public PersonDTO(long id, String email, String password, String name, String scope) {
        this.id = id;
        this.email = email;
        this.password = password;
        this.name = name;
        this.scope = scope;
    }

    public PersonDTO(long id, String email, String name, String scope) {
        this.id = id;
        this.email = email;
        this.name = name;
        this.scope = scope;
    }

    public PersonDTO() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getScope() {
        return scope;
    }

    public void setScope(String scope) {
        this.scope = scope;
    }
}

