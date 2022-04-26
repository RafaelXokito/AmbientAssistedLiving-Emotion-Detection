package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class EmotionDTO {

    private String name;
    private String group;

    public EmotionDTO() {
        this.name = "";
        this.group = "";
    }

    public EmotionDTO(String name) {
        this.name = name;
        this.group = "";
    }

    public EmotionDTO(String name, String group) {
        this.name = name;
        this.group = group;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getGroup() {
        return group;
    }

    public void setGroup(String group) {
        this.group = group;
    }
}