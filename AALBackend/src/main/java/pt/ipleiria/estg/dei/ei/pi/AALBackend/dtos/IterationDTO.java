package pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos;

public class IterationDTO {
    private long id;
    private String macAddress;
    private ClientDTO client;

    public IterationDTO(){

    }

    public IterationDTO(long id, String macAddress, ClientDTO client){
        this.id = id;
        this.macAddress = macAddress;
        this.client = client;
    }

    public IterationDTO(String macAddress, ClientDTO client){
        this.macAddress = macAddress;
        this.client = client;
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


}
