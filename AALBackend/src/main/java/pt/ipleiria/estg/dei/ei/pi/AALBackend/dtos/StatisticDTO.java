package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class StatisticDTO {
    private String name;
    private String value;
    private String subValue;

    public StatisticDTO(String name, String value) {
        this.name = name;
        this.value = value;
    }

    public StatisticDTO(String name, String value, String subValue) {
        this.name = name;
        this.value = value;
        this.subValue = subValue;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getValue() {
        return value;
    }

    public void setValue(String value) {
        this.value = value;
    }

    public String getSubValue() {
        return subValue;
    }

    public void setSubValue(String subValue) {
        this.subValue = subValue;
    }
}
