package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class FrameDTO {
    private long id;
    private String filename;
    private String filepath;

    public FrameDTO(long id, String filename, String filepath) {
        this.id = id;
        this.filename = filename;
        this.filepath = filepath;
    }

    public FrameDTO() {
        this.id = -1;
        this.filename = "";
        this.filepath = "";
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getFilename() {
        return filename;
    }

    public void setFilename(String filename) {
        this.filename = filename;
    }

    public String getFilepath() {
        return filepath;
    }

    public void setFilepath(String filepath) {
        this.filepath = filepath;
    }

}

