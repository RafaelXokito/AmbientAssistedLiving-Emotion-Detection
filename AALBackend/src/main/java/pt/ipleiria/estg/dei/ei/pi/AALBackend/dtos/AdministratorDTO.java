package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class AdministratorDTO {
    private long id;
    private String email;
    private String password;
    private String name;

    public AdministratorDTO(long id, String email, String password, String name) {
        this.id = id;
        this.email = email;
        this.password = password;
        this.name = name;
    }

    public AdministratorDTO(String email, String password, String name) {
        this.email = email;
        this.password = password;
        this.name = name;
    }

    public AdministratorDTO(long id, String email, String name) {
        this.id = id;
        this.email = email;
        this.name = name;
    }

    public AdministratorDTO() {
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
}