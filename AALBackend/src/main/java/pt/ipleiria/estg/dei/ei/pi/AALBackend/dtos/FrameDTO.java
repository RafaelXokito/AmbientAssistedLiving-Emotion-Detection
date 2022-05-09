package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.Date;

public class FrameDTO {
    private long id;
    private String filename;
    private String filepath;
    private EmotionDTO emotion;
    private Date createDate;

    public FrameDTO(long id, String filename, String filepath, Date createDate) {
        this.id = id;
        this.filename = filename;
        this.filepath = filepath;
        this.emotion = new EmotionDTO();
        this.createDate = createDate;
    }
    public FrameDTO(long id, String filename, String filepath, EmotionDTO emotion) {
        this.id = id;
        this.filename = filename;
        this.filepath = filepath;
        this.emotion = emotion;
    }

    public FrameDTO() {
        this.id = -1;
        this.filename = "";
        this.filepath = "";
        this.emotion = new EmotionDTO();
        this.createDate = new Date();
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

    public EmotionDTO getEmotion() {
        return emotion;
    }

    public void setEmotion(EmotionDTO emotion) {
        this.emotion = emotion;
    }

    public Date getCreateDate() {
        return createDate;
    }

    public void setCreateDate(Date createDate) {
        this.createDate = createDate;
    }
}

