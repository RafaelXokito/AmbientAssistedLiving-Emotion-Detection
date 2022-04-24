package pt.ipleiria.estg.dei.ei.pi.AALBackend.jwt;

public class Jwt {
    private String type;
    private String token;
    public Jwt(String type, String token) {
        this.type = type;
        this.token = token;
    }
    public void setToken(String token) {
        this.token = token;
    }
    public String getToken() {
        return token;
    }

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }
}
