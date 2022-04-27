package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.Date;

public class ClientDTO {
    private long id;
    private String email;
    private String name;
    private String password;
    private Date birthDate;
    private String contact;
    private AdministratorDTO administrator;

    public ClientDTO() {
        this.id = -1;
        this.email = "";
        this.name = "";
        this.password = "";
        this.birthDate = new Date();
        this.contact = "";
        this.administrator = new AdministratorDTO();
    }

    public ClientDTO(long id, String email, String name, Date birthDate, String contact) {
        this.id = id;
        this.email = email;
        this.name = name;
        this.birthDate = birthDate;
        this.contact = contact;
        this.administrator = new AdministratorDTO();
    }

    public ClientDTO(long id, String email, String name, Date birthDate, String contact, AdministratorDTO administrator) {
        this.id = id;
        this.email = email;
        this.name = name;
        this.birthDate = birthDate;
        this.contact = contact;
        this.administrator = administrator;
    }

    public ClientDTO(String email, String name, Date birthDate, String contact) {
        this.email = email;
        this.name = name;
        this.birthDate = birthDate;
        this.contact = contact;
    }

    public ClientDTO(String email, String password, String name, Date birthDate, String contact) {
        this.email = email;
        this.name = name;
        this.password = password;
        this.birthDate = birthDate;
        this.contact = contact;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public Date getBirthDate() {
        return birthDate;
    }

    public void setBirthDate(Date birthDate) {
        this.birthDate = birthDate;
    }

    public String getContact() {
        return contact;
    }

    public void setContact(String contact) {
        this.contact = contact;
    }
}
