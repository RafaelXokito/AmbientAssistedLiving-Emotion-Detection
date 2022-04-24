package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class ClientDTO {
    private long id;
    private String email;
    private String name;
    private String password;
    private int age;
    private String contact;

    public ClientDTO() {
    }

    public ClientDTO(String email, String password, String name, int age, String contact) {
        this.email = email;
        this.name = name;
        this.password = password;
        this.age = age;
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
}
