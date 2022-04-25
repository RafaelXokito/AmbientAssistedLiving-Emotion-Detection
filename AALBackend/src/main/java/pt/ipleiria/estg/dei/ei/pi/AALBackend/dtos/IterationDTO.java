package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.ArrayList;
import java.util.Date;
import java.util.List;

public class IterationDTO {
    private long id;
    private String macAddress;
    private String emotion;
    private ClientDTO client;
    private List<FrameDTO> frames;
    private Date created_at;

    public IterationDTO(){
        this.id = -1;
        this.macAddress = "";
        this.emotion = "";
        this.created_at = new Date();
        this.client = new ClientDTO();
        this.frames = new ArrayList<>();
    }

    public IterationDTO(long id, String macAddress, String emotion, Date created_at, ClientDTO client){
        this.id = id;
        this.macAddress = macAddress;
        this.emotion = emotion;
        this.created_at = created_at;
        this.client = client;
        this.frames = new ArrayList<>();
    }

    public IterationDTO(long id, String macAddress, String emotion, Date created_at, ClientDTO client, List<FrameDTO> frames){
        this.id = id;
        this.macAddress = macAddress;
        this.emotion = emotion;
        this.created_at = created_at;
        this.client = client;
        this.frames = frames;
    }

    public IterationDTO(long id, String macAddress, String emotion, Date created_at){
        this.id = id;
        this.macAddress = macAddress;
        this.emotion = emotion;
        this.created_at = created_at;
        this.client = new ClientDTO();
        this.frames = new ArrayList<>();
    }

    public IterationDTO(String macAddress, String emotion, Date created_at, ClientDTO client){
        this.id = -1;
        this.macAddress = macAddress;
        this.emotion = emotion;
        this.created_at = created_at;
        this.client = client;
        this.frames = new ArrayList<>();
    }

    public long getId(){
        return id;
    }

    public void setId(long id){
        this.id = id;
    }

    public ClientDTO getClient() {
        return client;
    }

    public void setClient(ClientDTO client) {
        this.client = client;
    }

    public String getMacAddress() {
        return macAddress;
    }

    public void setMacAddress(String macAddress) {
        this.macAddress = macAddress;
    }

    public String getEmotion() {
        return emotion;
    }

    public void setEmotion(String emotion) {
        this.emotion = emotion;
    }

    public List<FrameDTO> getFrames(){
        return frames;
    }

    public void setFrames(List<FrameDTO> frames){
        this.frames = frames;
    }

    public Date getCreated_at() {
        return created_at;
    }

    public void setCreated_at(Date created_at) {
        this.created_at = created_at;
    }

}
