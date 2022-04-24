package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

import java.util.ArrayList;
import java.util.List;

public class IterationDTO {
    private long id;
    private String macAddress;
    private ClientDTO client;
    private List<FrameDTO> frames;

    public IterationDTO(){
        this.id = -1;
        this.macAddress = "";
        this.client = new ClientDTO();
        this.frames = new ArrayList<>();
    }

    public IterationDTO(long id, String macAddress, ClientDTO client){
        this.id = id;
        this.macAddress = macAddress;
        this.client = client;
        this.frames = new ArrayList<>();
    }

    public IterationDTO(long id, String macAddress, ClientDTO client, List<FrameDTO> frames){
        this.id = id;
        this.macAddress = macAddress;
        this.client = client;
        this.frames = frames;
    }

    public IterationDTO(String macAddress, ClientDTO client){
        this.id = -1;
        this.macAddress = macAddress;
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

    public List<FrameDTO> getFrames(){
        return frames;
    }

    public void setFrames(List<FrameDTO> frames){
        this.frames = frames;
    }

}
